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
    return true;
  }

  protected static function RunCommand() {
    if (RQ::Fetch()->create_room) {
      RoomEntry::Execute();
    } elseif (RQ::Fetch()->change_room) {
      RoomEntry::Execute();
    } elseif (RQ::Fetch()->describe_room) {
      RoomDescribe::Execute();
    } elseif (RQ::Get(RequestDataGame::ID) > 0) {
      RoomEntry::Output();
    } else {
      self::OutputList();
    }
  }

  protected static function Finish() {
    DB::Disconnect();
  }

  //稼働中の村リスト出力
  private static function OutputList() {
    if (ServerConfig::SECRET_ROOM) { //シークレットテストモード
      return;
    }

    foreach (RoomManagerDB::GetList() as $stack) {
      RoomManagerHTML::OutputRoom($stack);
    }
  }
}
