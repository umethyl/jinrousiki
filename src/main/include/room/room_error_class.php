<?php
//-- 村作成/オプション説明時エラー処理クラス --//
final class RoomError {
  //オプション変更時エラー出力
  public static function Output(string $str) {
    HTML::OutputResult(self::GetTitle(RoomManagerMessage::TITLE_CHANGE), $str);
  }

  //タイトル取得
  public static function GetTitle(string $str) {
    return $str . ' ' . Message::ERROR_TITLE;
  }

  //対象村取得
  public static function GetRoom(string $str) {
    return DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER . $str;
  }
}
