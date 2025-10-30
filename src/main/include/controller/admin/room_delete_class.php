<?php
//--  村削除(管理用)コントローラー --//
final class JinrouAdminRoomDeleteController extends JinrouAdminController {
  protected static function GetAdminType() {
    return 'room_delete';
  }

  protected static function LoadRequest() {
    RQ::LoadRequest();
    RQ::Fetch()->ParseGetRoomNo();
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
    if (true === DB::Lock('room') && DB::DeleteRoom(RQ::Fetch()->room_no)) {
      DB::Commit();
      //DB::Optimize(); //遅いのでオフにしておく (オンにする場合は Commit() と差し替え)

      $str = RQ::Fetch()->room_no . RoomDeleteMessage::SUCCESS;
      HTML::OutputResult(RoomDeleteMessage::TITLE, $str, '../');
    } else {
      $title = RoomDeleteMessage::TITLE . ' ' . Message::ERROR_TITLE;
      HTML::OutputResult($title, RQ::Fetch()->room_no . RoomDeleteMessage::FAILED);
    }
  }
}
