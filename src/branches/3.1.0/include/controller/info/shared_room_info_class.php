<?php
//-- 関連サーバ村情報出力クラス --//
class SharedRoomInfo {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('shared_room');
  }

  //出力
  private static function Output() {
    if (0 < RQ::Get()->id && RQ::Get()->id <= count(SharedServerConfig::$server_list)) {
      InfoHTML::OutputSharedRoom(RQ::Get()->id);
    } else {
      InfoHTML::OutputHeader(SharedRoomInfoMessage::TITLE, 0, 'shared_room');
      InfoHTML::OutputSharedRoomList();
      HTML::OutputFooter();
    }
  }
}
