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
    $room = RoomManagerDB::Load();
    if (true !== $room instanceof Room) {
      self::OutputError(Message::INVALID_ROOM);
    }
    DB::SetRoom($room);

    if (DB::$ROOM->IsFinished()) {
      self::OutputError(RoomError::GetRoom(RoomErrorMessage::FINISHED));
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
  private static function OutputError(string $str) {
    HTML::OutputResult(RoomError::GetTitle(RoomDescribeMessage::TITLE), $str);
  }
}
