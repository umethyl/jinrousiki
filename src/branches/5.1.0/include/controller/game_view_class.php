<?php
//-- ◆文字化け抑制◆ --//
//-- 観戦画面コントローラー --//
final class GameViewController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'game_view';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function GetLoadDatabaseID() {
    return RQ::Get()->db_no;
  }

  protected static function EnableLoadRoom() {
    return true;
  }

  protected static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->SetFlag(RoomMode::VIEW);
    DB::$ROOM->SetTime();

    //シーン別調整
    if (DB::$ROOM->IsBeforeGame()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene); //投票済み情報
    }
  }

  protected static function LoadUser() {
    DB::LoadUser();
  }

  protected static function LoadSelf() {
    DB::LoadViewer();
  }

  protected static function Output() {
    GameViewHTML::Output();
  }
}
