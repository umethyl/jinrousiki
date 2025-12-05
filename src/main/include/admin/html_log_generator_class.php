<?php
//-- ログのHTML化実行クラス --//
final class JinrouHTMLLogGenerator {
  //設定ロード
  public static function Load() {
    RQ::Set('prefix',		GenerateHTMLLogConfig::PREFIX);
    RQ::Set('index_no',		GenerateHTMLLogConfig::INDEX_START);
    RQ::Set('min_room_no',	GenerateHTMLLogConfig::ROOM_START);
    RQ::Set('max_room_no',	GenerateHTMLLogConfig::ROOM_END);
    RQ::Set(RequestDataLogRoom::ADD_ROLE, true);
    RQ::Set(RequestDataLogRoom::HEAVEN,   true);
    RQ::Set('generate_index', true);
  }

  //実行
  public static function Execute() {
    //-- Validate --//
    if (self::GetMin() < 1) {
      $str = sprintf(GenerateHTMLLogMessage::INVALIDE_ROOM_START, self::GetMin());
      return self::OutputResult($str);
    }

    if (self::GetMax() < 1 || self::GetMin() > self::GetMax()) {
      $str = sprintf(GenerateHTMLLogMessage::INVALIDE_ROOM_END, self::GetMin(), self::GetMax());
      return self::OutputResult($str);
    }

    //-- モード判定 --//
    switch (GenerateHTMLLogConfig::MODE) {
    case 'room':
      return self::GenerateRoom();

    case 'index':
      return self::GenerateIndex();

    case 'delete':
      return self::DeleteRoom();

    default: //ここに来たらロジックエラー
      return self::OutputResult(GenerateHTMLLogMessage::INVALIDE_MODE);
    }
  }

  //個別の部屋のHTML化処理
  private static function GenerateRoom() {
    $format = sprintf('../%s/%s', GenerateHTMLLogConfig::DIR, RQ::Get('prefix')) . '%d%s.html';
    $footer = Text::LineFeed(HTML::GenerateFooter());
    for ($i = self::GetMin(); $i <= self::GetMax(); $i++) {
      RQ::Set(RequestDataGame::ID, $i);
      foreach ([false, true] as $flag) {
	RQ::Set(RequestDataLogRoom::REVERSE_LOG, $flag);

	DB::LoadRoom();
	DB::$ROOM->SetFlag(RoomMode::LOG);
	DB::$ROOM->SetLastDate();

	DB::LoadUser();
	DB::LoadViewer();

	$file = sprintf($format, $i, $flag ? 'r' : '');
	file_put_contents($file, OldLogHTML::Generate() . $footer);
      }
    }

    $format = GenerateHTMLLogMessage::FORMAT;
    self::OutputResult(sprintf($format, self::GetMin(), self::GetMax()));
  }

  //過去ログ一覧のHTML化処理
  private static function GenerateIndex() {
    RQ::Set(RequestDataLogRoom::REVERSE_LIST, Switcher::OFF);
    $header = sprintf('../%s/%sindex', GenerateHTMLLogConfig::DIR, RQ::Get('prefix'));
    $footer = Text::LineFeed(HTML::GenerateFooter());
    $end_page = ceil((self::GetMax() - self::GetMin() + 1) / OldLogConfig::VIEW);
    for ($i = 1; $i <= $end_page; $i++) {
      RQ::Set('page', $i);
      $index = RQ::Fetch()->index_no - $i + 1;
      file_put_contents($header. $index . '.html', LogListHTML::Generate($i) . $footer);
    }

    $format = GenerateHTMLLogMessage::FORMAT;
    self::OutputResult(sprintf($format, self::GetMin(), self::GetMax()));
  }

  //部屋削除
  private static function DeleteRoom() {
    HTML::OutputHeader(GenerateHTMLLogMessage::DELETE_TITLE, null, true);
    for ($i = self::GetMin(); $i <= self::GetMax(); $i++) {
      DB::DeleteRoom($i);
      printf(GenerateHTMLLogMessage::DELETE_FORMAT . Text::BR, $i);
    }
    DB::Optimize();
    HTML::OutputFooter(true);
  }

  //対象部屋番号取得(最小)
  private static function GetMin() {
    return RQ::Get('min_room_no');
  }

  //対象部屋番号取得(最大)
  private static function GetMax() {
    return RQ::Get('max_room_no');
  }

  //結果出力
  private static function OutputResult(string $str) {
    HTML::OutputResult(GenerateHTMLLogMessage::TITLE, $str);
  }
}
