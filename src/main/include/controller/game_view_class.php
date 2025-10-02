<?php
//◆文字化け抑制◆//
//-- 観戦画面コントローラー --//
final class GameViewController extends JinrouController {
  protected static function Load() {
    RQ::LoadRequest('game_view');
    DB::Connect(RQ::Get()->db_no);

    //村情報ロード
    DB::LoadRoom();
    DB::$ROOM->SetFlag(RoomMode::VIEW);
    DB::$ROOM->system_time = Time::Get();

    //シーン別調整
    if (DB::$ROOM->IsBeforeGame()) {
      RQ::Set('retrieve_type', DB::$ROOM->scene); //投票済み情報
    }

    //ユーザ情報ロード
    DB::LoadUser();
    DB::LoadViewer();
  }

  protected static function Output() {
    GameViewHTML::Output();
  }
}
