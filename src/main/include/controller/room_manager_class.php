<?php
//-- 村作成コントローラー --//
final class RoomManagerController extends JinrouController {
  protected static function Maintenance() {
    if (false === DB::ConnectInHeader()) { //ここでDB接続を行う
      return;
    }

    if (ServerConfig::DISABLE_MAINTENANCE) {
      return;
    }

    if (Loader::IsLoadedFile('index_class')) {
      RoomManagerDB::DieRoom();		//一定時間更新の無い村は廃村にする
      RoomManagerDB::ClearSession();	//終了した村のセッションデータを削除する
    }
  }

  protected static function GetLoadRequest() {
    return 'room_manager';
  }

  protected static function EnableCommand() {
    switch (true) {
    case RQ::Fetch()->create_room:
    case RQ::Fetch()->change_room:
    case RQ::Fetch()->describe_room:
    case (RQ::Get(RequestDataGame::ID) > 0):
      return true;

    default:
      return false;
    }
  }

  protected static function RunCommand() {
    switch (true) {
    case RQ::Fetch()->create_room:
    case RQ::Fetch()->change_room:
      RoomEntry::Execute();
      return;

    case RQ::Fetch()->describe_room:
      RoomDescribe::Execute();
      return;

    case (RQ::Get(RequestDataGame::ID) > 0):
      RoomEntry::Output();
      return;
    }
  }

  protected static function Output() {
    if (ServerConfig::SECRET_ROOM) { //シークレットテストモード
      return;
    }
    RoomManagerHTML::Output();
  }

  protected static function Finish() {
    DB::Disconnect();
  }
}
