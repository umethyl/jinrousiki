<?php
//--  ログのHTML化(管理用)コントローラー --//
final class JinrouAdminGenerateHTMLLogController extends JinrouAdminController {
  protected static function GetAdminType() {
    return 'generate_html_log';
  }

  protected static function LoadRequest() {
    RQ::LoadRequest('old_log');
    RQ::Set('prefix',		GenerateHTMLLogConfig::PREFIX);
    RQ::Set('index_no',		GenerateHTMLLogConfig::INDEX_START);
    RQ::Set('min_room_no',	GenerateHTMLLogConfig::ROOM_START);
    RQ::Set('max_room_no',	GenerateHTMLLogConfig::ROOM_END);
    RQ::Set(RequestDataLogRoom::ROLE,   true);
    RQ::Set(RequestDataLogRoom::HEAVEN, true);
    RQ::Set('generate_index', true);
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function GetLoadDatabaseID() {
    return RQ::Get()->db_no;
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
    //-- Validate --//
    if (RQ::Get()->min_room_no < 1) {
      $str = sprintf(GenerateHTMLLogMessage::INVALIDE_ROOM_START, RQ::Get()->min_room_no);
      return self::OutputResult($str);
    }

    if (RQ::Get()->max_room_no < 1 || RQ::Get()->min_room_no > RQ::Get()->max_room_no) {
      $str = sprintf(
	GenerateHTMLLogMessage::INVALIDE_ROOM_END,
	RQ::Get()->min_room_no, RQ::Get()->max_room_no
      );
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
    $format = sprintf('../%s/%s', GenerateHTMLLogConfig::DIR, RQ::Get()->prefix) . '%d%s.html';
    $footer = Text::LineFeed(HTML::GenerateFooter());
    for ($i = RQ::Get()->min_room_no; $i <= RQ::Get()->max_room_no; $i++) {
      RQ::Set(RequestDataGame::ID, $i);
      foreach ([false, true] as $flag) {
	RQ::Set(RequestDataLogRoom::REVERSE, $flag);

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
    self::OutputResult(sprintf($format, RQ::Get()->min_room_no, RQ::Get()->max_room_no));
  }

  //過去ログ一覧のHTML化処理
  private static function GenerateIndex() {
    RQ::Set('reverse', Switcher::OFF);
    $header = sprintf('../%s/%sindex', GenerateHTMLLogConfig::DIR, RQ::Get()->prefix);
    $footer = Text::LineFeed(HTML::GenerateFooter());
    $end_page = ceil((RQ::Get()->max_room_no - RQ::Get()->min_room_no + 1) / OldLogConfig::VIEW);
    for ($i = 1; $i <= $end_page; $i++) {
      RQ::Set('page', $i);
      $index = RQ::Get()->index_no - $i + 1;
      file_put_contents($header. $index . '.html', OldLogHTML::GenerateList($i) . $footer);
    }

    $format = GenerateHTMLLogMessage::FORMAT;
    self::OutputResult(sprintf($format, RQ::Get()->min_room_no, RQ::Get()->max_room_no));
  }

  //部屋削除
  private static function DeleteRoom() {
    HTML::OutputHeader(GenerateHTMLLogMessage::DELETE_TITLE, null, true);
    for ($i = RQ::Get()->min_room_no; $i <= RQ::Get()->max_room_no; $i++) {
      DB::DeleteRoom($i);
      printf(GenerateHTMLLogMessage::DELETE_FORMAT . Text::BR, $i);
    }
    DB::Optimize();
    HTML::OutputFooter(true);
  }

  //結果出力
  private static function OutputResult(string $str) {
    HTML::OutputResult(GenerateHTMLLogMessage::TITLE, $str);
  }
}
