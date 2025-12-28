<?php
//-- ◆文字化け抑制◆ --//
//-- GameLog コントローラー --//
final class GameLogController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'game_log';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::Certify();
  }

  protected static function EnableLoadRoom() {
    return true;
  }

  protected static function LoadRoom() {
    DB::LoadRoom();
    DB::$ROOM->SetFlag(RoomMode::LOG);
  }

  protected static function LoadUser() {
    DB::LoadUser();
  }

  protected static function LoadSelf() {
    DB::LoadSelf();
  }

  protected static function LoadExtra() {
    //シーンチェック
    switch (RQ::Fetch()->scene) {
    case RoomScene::HEAVEN:
      if (false === self::EnableHeaven()) {
	self::OutputError(GameLogMessage::PLAYING);
      }
      break;

    case RoomScene::AFTER:
      if (false === DB::$ROOM->IsFinished()) { //ゲーム終了後判定
	self::OutputError(GameLogMessage::PLAYING);
      }
      break;

    default:
      if (true === self::IsFuture()) { //未来判定
	self::OutputError(GameLogMessage::FUTURE);
      }
      DB::$ROOM->SetLastDate();
      DB::$ROOM->SetDate(RQ::Fetch()->date);
      DB::$ROOM->SetScene(RQ::Fetch()->scene);
      DB::$USER->SetEvent(true);
      break;
    }
  }

  protected static function Output() {
    GameLogHTML::Output();
  }

  //霊界閲覧有効判定 (身代わり君生存中 or ゲーム終了後)
  private static function EnableHeaven() {
    return (DB::$SELF->IsDummyBoy() && DB::$SELF->IsLive()) || DB::$ROOM->IsFinished();
  }

  //未来判定
  private static function IsFuture() {
    if (DateBorder::Future(RQ::Fetch()->date)) {
      return true;
    }

    if (DateBorder::On(RQ::Fetch()->date)) {
      return (DB::$ROOM->IsDay() || DB::$ROOM->scene == RQ::Fetch()->scene);
    }
    return false;
  }

  //エラー処理
  private static function OutputError(string $str) {
    HTML::OutputResult(GameLogMessage::INPUT, GameLogMessage::INPUT . $str);
  }
}
