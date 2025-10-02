<?php
//-- 管理用クラス --//
class JinrouAdmin {
  //村削除
  static function DeleteRoom() {
    if (! ServerConfig::DEBUG_MODE) HTML::OutputUnusableError();
    Loader::LoadRequest();
    RQ::Get()->ParseGetRoomNo();

    DB::Connect();
    if (DB::Lock('room') && DB::DeleteRoom(RQ::Get()->room_no)) {
      DB::Optimize();
      $str = RQ::Get()->room_no . AdminMessage::DELETE_ROOM_SUCCESS;
      HTML::OutputResult(AdminMessage::DELETE_ROOM, $str, '../');
    }
    else {
      $title = AdminMessage::DELETE_ROOM . ' ' . Message::ERROR_TITLE;
      HTML::OutputResult($title, RQ::Get()->room_no . AdminMessage::DELETE_ROOM_FAILED);
    }
  }

  //アイコン削除
  static function DeleteIcon() {
    if (! ServerConfig::DEBUG_MODE) HTML::OutputUnusableError();
    Loader::LoadRequest();
    RQ::Get()->ParseGetInt('icon_no');
    $icon_no = RQ::Get()->icon_no;
    $title   = AdminMessage::DELETE_ICON . ' ' . Message::ERROR_TITLE;
    if ($icon_no < 1) {
      HTML::OutputResult($title, sprintf(IconEditMessage::NOT_EXISTS, $icon_no));
    }

    Loader::LoadFile('icon_functions');
    DB::Connect();
    if (! DB::Lock('icon')) HTML::OutputResult($title, Message::DB_ERROR_LOAD);

    //使用中判定
    if (IconDB::IsUsing($icon_no)) HTML::OutputResult($title, IconEditMessage::USING);

    $file = IconDB::GetFile($icon_no); //存在判定
    if ($file === false || is_null($file)) {
      HTML::OutputResult($title, AdminMessage::DELETE_ICON_NOTHING);
    }

    if (IconDB::Delete($icon_no, $file)) {
      $url = '../icon_upload.php';
      $str = AdminMessage::DELETE_ICON_SUCCESS . Text::BRLF . sprintf(Message::JUMP, $url);
      HTML::OutputResult(AdminMessage::DELETE_ICON, $str, $url);
    }
    else {
      HTML::OutputResult($title, Message::DB_ERROR_LOAD);
    }
  }

  //ログ生成
  static function GenerateLog() {
    $format = sprintf('../log_test/%s', RQ::Get()->prefix) . '%d%s.html';
    $footer = HTML::FOOTER . Text::LF;
    for ($i = RQ::Get()->min_room_no; $i <= RQ::Get()->max_room_no; $i++) {
      RQ::Set('room_no', $i);
      foreach (array(false, true) as $flag) {
	RQ::Set('reverse_log', $flag);

	DB::LoadRoom();
	DB::$ROOM->SetFlag('log_mode');
	DB::$ROOM->last_date = DB::$ROOM->date;

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
  static function DeleteLog($from, $to) {
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
