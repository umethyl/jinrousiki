<?php
//-- 村作成/オプション説明時エラー処理クラス --//
final class RoomError {
  //-- 種別 --//
  const EMPTY    = 'empty';
  const COMMENT  = 'comment';
  const INPUT    = 'input';
  const TIME     = 'time';
  const USER     = 'user';
  const PASSWORD = 'password';
  const BUSY     = 'busy';

  //登録時
  public static function Entry(string $type, string $str = '') {
    RoomErrorHTML::Output($type, $str);
  }

  //制限事項
  public static function Limit(string $str) {
    $title = sprintf(RoomErrorMessage::TITLE, RoomErrorMessage::LIMIT);
    HTML::OutputResult($title, $str);
  }

  //制限事項(要待機)
  public static function Wait(string $str) {
    self::Limit(Text::Join($str, RoomErrorMessage::LIMIT_WAIT_FINISH));
  }

  //オプション変更時
  public static function Change(string $str) {
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
