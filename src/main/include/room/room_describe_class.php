<?php
//-- 村オプション説明クラス --//
final class RoomDescribe {
  //実行
  public static function Execute() {
    self::Load();
    RoomDescribeHTML::Output();
  }

  //情報ロード
  private static function Load() {
    self::LoadRequest();
    self::LoadRoom();
    self::LoadOption();
  }

  //リクエストチェック
  private static function LoadRequest() {
    if (RQ::Get(RequestDataGame::ID) < 1) {
      self::OutputError(Message::INVALID_ROOM);
    }
  }

  //村情報ロード
  private static function LoadRoom() {
    DB::SetRoom(RoomManagerDB::Load());
    if (DB::$ROOM->id < 1) {
      self::OutputError(Message::INVALID_ROOM);
    }
    if (DB::$ROOM->IsFinished()) {
      self::OutputError(RoomError::GetRoom(RoomManagerMessage::ERROR_FINISHED));
    }
  }

  //オプションロード
  private static function LoadOption() {
    $stack = [
      'game_option' => DB::$ROOM->game_option,
      'option_role' => DB::$ROOM->option_role,
      'max_user'    => DB::$ROOM->max_user
    ];
    RoomOptionLoader::Load($stack);
  }

  //エラー出力
  private static function OutputError($body) {
    HTML::OutputResult(RoomError::GetTitle(RoomManagerMessage::TITLE_DESCRIBE), $body);
  }
}
