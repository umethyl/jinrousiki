<?php
//-- HTML 生成クラス (RoomManager 拡張) --//
final class RoomManagerHTML {
  //出力
  public static function Output() {
    foreach (RoomManagerDB::GetList() as $stack) {
      RoomManagerHTML::OutputRoom($stack);
    }
  }

  //村情報出力
  private static function OutputRoom(array $stack) {
    $ROOM = new Room();
    $ROOM->LoadData($stack);
    RoomOptionLoader::Load($stack);
    if (AdminConfig::$room_delete_enable) {
      $url    = URL::GetRoom('admin/room_delete', $ROOM->id);
      $delete = Text::QuoteBracket(LinkHTML::Generate($url, RoomManagerMessage::DELETE));
    } else {
      $delete = '';
    }

    switch ($ROOM->status) {
    case RoomStatus::WAITING:
      $status = RoomManagerMessage::WAITING;
      break;

    case RoomStatus::CLOSING:
      $status = RoomManagerMessage::CLOSING;
      break;

    case RoomStatus::PLAYING:
      $status = RoomManagerMessage::PLAYING;
      break;
    }

    Text::Printf(self::GetRoom(),
      $delete, URL::GetRoom('login', $ROOM->id),
      ImageManager::Room()->Generate($ROOM->status, $status),
      $ROOM->GenerateNumber(),  $ROOM->GenerateName(),  Text::BR,
      $ROOM->GenerateComment(), RoomOptionLoader::Generate(), Text::BR
    );
  }

  //村タグ
  private static function GetRoom() {
    return '%s<a href="%s">%s <span>[%s]</span>%s%s<div>%s %s</div></a>%s';
  }
}
