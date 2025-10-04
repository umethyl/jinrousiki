<?php
//-- 管理用クラス --//
class JinrouAdmin {
  //ログ生成
  public static function GenerateLog() {
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

    $title  = AdminMessage::GENERATE_LOG;
    $format = AdminMessage::GENERATE_LOG_FORMAT;
    HTML::OutputResult($title, sprintf($format, RQ::Get()->min_room_no, RQ::Get()->max_room_no));
  }

  //ログ削除
  public static function DeleteLog($from, $to) {
    DB::Connect(RQ::Get()->db_no);
    HTML::OutputHeader(AdminMessage::DELETE_LOG, null, true);
    for ($i = $from; $i <= $to; $i++) {
      DB::DeleteRoom($i);
      printf(AdminMessage::DELETE_LOG_FORMAT . Text::BR, $i);
    }
    DB::Optimize();
    HTML::OutputFooter(true);
  }
}
