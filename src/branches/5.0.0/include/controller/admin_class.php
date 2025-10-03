<?php
//-- 管理用クラス --//
class JinrouAdmin {
  //村削除
  public static function DeleteRoom() {
    if (true !== ServerConfig::DEBUG_MODE) {
      HTML::OutputUnusableError();
    }

    RQ::LoadRequest();
    RQ::Get()->ParseGetRoomNo();

    DB::Connect();
    if (true === DB::Lock('room') && DB::DeleteRoom(RQ::Get()->room_no)) {
      DB::Commit();
      //DB::Optimize(); //遅いのでオフにしておく (オンにする場合は Commit() と差し替え)
      $str = RQ::Get()->room_no . AdminMessage::DELETE_ROOM_SUCCESS;
      HTML::OutputResult(AdminMessage::DELETE_ROOM, $str, '../');
    } else {
      $title = AdminMessage::DELETE_ROOM . ' ' . Message::ERROR_TITLE;
      HTML::OutputResult($title, RQ::Get()->room_no . AdminMessage::DELETE_ROOM_FAILED);
    }
  }

  //アイコン削除
  public static function DeleteIcon() {
    if (true !== ServerConfig::DEBUG_MODE) {
      HTML::OutputUnusableError();
    }

    RQ::LoadRequest();
    RQ::Get()->ParseGetInt(RequestDataIcon::ID);
    $icon_no = RQ::Get()->icon_no;
    $title   = AdminMessage::DELETE_ICON . ' ' . Message::ERROR_TITLE;
    if ($icon_no < 1) {
      HTML::OutputResult($title, sprintf(IconMessage::NOT_EXISTS, $icon_no));
    }

    DB::Connect();
    if (false === DB::Lock('icon')) {
      HTML::OutputResult($title, Message::DB_ERROR_LOAD);
    }

    if (IconDB::Using($icon_no)) { //使用中判定
      HTML::OutputResult($title, IconMessage::USING);
    }

    $file = IconDB::GetFile($icon_no); //存在判定
    if (false === $file || null === $file) {
      HTML::OutputResult($title, AdminMessage::DELETE_ICON_NOT_EXISTS);
    }

    if (IconDB::Delete($icon_no, $file)) {
      $url = '../icon_upload.php';
      $str = Text::Join(AdminMessage::DELETE_ICON_SUCCESS, URL::GetJump($url));
      HTML::OutputResult(AdminMessage::DELETE_ICON, $str, $url);
    } else {
      HTML::OutputResult($title, Message::DB_ERROR_LOAD);
    }
  }

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
