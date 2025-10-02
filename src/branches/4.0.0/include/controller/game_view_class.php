<?php
//◆文字化け抑制◆//
//-- 観戦画面コントローラー --//
final class GameViewController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('game_view', true);
    DB::Connect(RQ::Get()->db_no);

    //村情報ロード
    DB::LoadRoom();
    DB::$ROOM->SetFlag(RoomMode::VIEW);
    DB::$ROOM->system_time = Time::Get();

    //シーンに応じた追加クラスをロード
    if (DB::$ROOM->IsFinished()) { //勝敗結果表示
      Loader::LoadFile('winner_message');
    } else { //ゲームオプション表示
      Loader::LoadFile('cast_config', 'image_class', 'room_option_class');
      if (DB::$ROOM->IsBeforeGame()) {
	RQ::Set('retrieve_type', DB::$ROOM->scene);
      }
    }

    //ユーザ情報ロード
    DB::LoadUser();
    DB::LoadViewer();
  }

  protected static function Output() {
    GameViewHTML::Output();
  }
}
