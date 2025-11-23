<?php
//-- ◆文字化け抑制◆ --//
//-- GamePlay コントローラー --//
final class GamePlayController extends JinrouController {
  private static $view;

  protected static function GetLoadRequest() {
    return 'game_play';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::LoginGamePlay();
  }

  protected static function EnableLoadRoom() {
    return true;
  }

  protected static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->Flag()->Set(RoomMode::DEAD,   RQ::Fetch()->dead_mode);
    DB::$ROOM->Flag()->Set(RoomMode::HEAVEN, RQ::Fetch()->heaven_mode);
    DB::$ROOM->SetTime();
    DB::$ROOM->InitializeSuddenDeath();

    //-- シーンに応じた追加クラスをロード --//
    self::$view = GamePlay::LoadView();
  }

  protected static function LoadUser() {
    DB::LoadUser();
  }

  protected static function LoadSelf() {
    DB::LoadSelf();
  }

  protected static function LoadExtra() {
    //-- 音声情報 --//
    Objection::Set(); //「異議」ありセット判定
    if (RQ::Fetch()->play_sound) { //音でお知らせ
      JinrouCookie::Set(); //クッキー情報セット
    }

    //-- 身代わり君の個別発言投稿時調整 --//
    RQ::Set('individual_talk', false);
    if (GameAction::IsIndividual()) {
      RQ::Set('individual_talk', true);
      if (DB::$SELF->IsDead()) {
	//霊界からの投稿時は死亡フラグを立てて後続の処理を通す
	RQ::Fetch()->dead_mode = true;
	DB::$ROOM->Flag()->Set(RoomMode::DEAD, RQ::Fetch()->dead_mode);
      }
    }

    //-- リンク情報収集 --//
    RQ::Fetch()->StackIntParam(RequestDataGame::ID, false);
    RQ::Fetch()->StackIntParam(RequestDataGame::RELOAD);

    $stack = [
      RequestDataGame::SOUND,
      RequestDataGame::ICON,
      RequestDataGame::NAME,
      RequestDataGame::LIST,
      RequestDataGame::WORDS
    ];
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsDummyBoy()) {
      $stack[] = RequestDataGame::INDIVIDUAL;
    }
    if (GameConfig::ASYNC) {
      $stack[] = RequestDataGame::ASYNC;
    }

    foreach ($stack as $name) {
      RQ::Fetch()->StackOnParam($name);
    }

    foreach ([RoomMode::DEAD, RoomMode::HEAVEN] as $name) {
      RQ::Fetch()->StackOnValue($name . '_mode', DB::$ROOM->IsOn($name));
    }
  }

  protected static function Output() {
    GamePlay::Talk();
    self::$view->Output();
  }

  //実行 (非同期用)
  public static function ExecuteAsync() {
    self::Load();
    GamePlay::FilterSilence();
    self::$view->OutputAsync();
  }
}
