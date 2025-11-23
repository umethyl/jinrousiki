<?php
//-- 村作成/オプション説明時エラー処理クラス --//
final class RoomError {
  //タイトル取得
  public static function GetTitle($str) {
    return $str . ' ' . Message::ERROR_TITLE;
  }

  //対象村取得
  public static function GetRoom($str) {
    return DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER . $str;
  }
}
