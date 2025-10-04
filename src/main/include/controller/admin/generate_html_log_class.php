<?php
//--  ログのHTML化(管理用)コントローラー --//
final class JinrouAdminGenerateHTMLLogController extends JinrouAdminController {
  protected static function GetAdminType() {
    return 'generate_html_log';
  }

  protected static function LoadRequest() {
    RQ::LoadRequest('RequestOldLog'); //引数を取得
    RQ::Set('prefix', ''); //各ページの先頭につける文字列 (テスト / 上書き回避用)
    RQ::Set('index_no', 8); //インデックスページの開始番号
    RQ::Set('min_room_no', 351); //インデックス化する村の開始番号
    RQ::Set('max_room_no', 383); //インデックス化する村の終了番号
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
    //self::DeleteLog(RQ::Get()->min_room_no, RQ::Get()->max_room_no); //部屋削除

    //OldLogHTML::GenerateIndex(); //インデックスページ生成
    //HTML::OutputFooter(true);

    $format = sprintf('../log_test/%s', RQ::Get()->prefix) . '%d%s.html';
    $footer = Text::LineFeed(HTML::FOOTER);
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

    $title  = GenerateHTMLLogMessage::TITLE;
    $format = GenerateHTMLLogMessage::FORMAT;
    HTML::OutputResult($title, sprintf($format, RQ::Get()->min_room_no, RQ::Get()->max_room_no));
  }

  //部屋削除
  private static function DeleteLog($from, $to) {
    DB::Connect(RQ::Get()->db_no);
    HTML::OutputHeader(GenerateHTMLLogMessage::DELETE_TITLE, null, true);
    for ($i = $from; $i <= $to; $i++) {
      DB::DeleteRoom($i);
      printf(GenerateHTMLLogMessage::DELETE_FORMAT . Text::BR, $i);
    }
    DB::Optimize();
    HTML::OutputFooter(true);
  }
}
