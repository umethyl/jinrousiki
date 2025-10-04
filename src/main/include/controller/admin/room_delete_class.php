<?php
//--  村削除(管理用)コントローラー --//
final class JinrouAdminRoomDeleteController extends JinrouController {
  protected static function Start() {
    if (true !== ServerConfig::DEBUG_MODE) {
      HTML::OutputUnusableError();
    }
  }

  protected static function LoadRequest() {
    RQ::LoadRequest();
    RQ::Get()->ParseGetRoomNo();
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
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
}
