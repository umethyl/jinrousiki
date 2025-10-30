<?php
//◆文字化け抑制◆//
//-- 関連サーバ村情報コントローラー --//
final class SharedRoomInfoController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'shared_room';
  }

  protected static function Output() {
    if (Number::Within(RQ::Fetch()->id, 0, count(SharedServerConfig::$server_list))) {
      InfoHTML::OutputSharedRoom(RQ::Fetch()->id);
    } else {
      InfoHTML::OutputHeader(SharedRoomInfoMessage::TITLE, 0, 'shared_room');
      InfoHTML::OutputSharedRoomList();
      HTML::OutputFooter();
    }
  }
}
