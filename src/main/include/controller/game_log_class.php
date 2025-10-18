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
    switch (RQ::Get()->scene) {
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
      DB::$ROOM->SetDate(RQ::Get()->date);
      DB::$ROOM->SetScene(RQ::Get()->scene);
      DB::$USER->SetEvent(true);
      break;
    }
  }

  //霊界閲覧有効判定 (身代わり君生存中 or ゲーム終了後)
  private static function EnableHeaven() {
    return (DB::$SELF->IsDummyBoy() && DB::$SELF->IsLive()) || DB::$ROOM->IsFinished();
  }

  //未来判定
  private static function IsFuture() {
    if (DB::$ROOM->date < RQ::Get()->date) {
      return true;
    }

    if (DB::$ROOM->IsDate(RQ::Get()->date)) {
      return (DB::$ROOM->IsDay() || DB::$ROOM->scene == RQ::Get()->scene);
    }
    return false;
  }

  protected static function Output() {
    GameHTML::OutputHeader('game_log');
    self::OutputHeader();
    if (RQ::Get()->scene == RoomScene::HEAVEN) {
      DB::$ROOM->SetFlag(RoomMode::HEAVEN); //念のためセット
      Talk::OutputHeaven();
    } else {
      self::OutputAbility();
      Talk::Output();

      if (DB::$ROOM->IsPlaying()) { //プレイ中は投票結果・遺言・死者を表示
	GameHTML::OutputLastWords();
	GameHTML::OutputDead();
      } elseif (DB::$ROOM->IsAfterGame()) {
	GameHTML::OutputLastWords(true); //遺言 (昼終了時限定)
      }

      if (DB::$ROOM->IsNight()) {
	GameHTML::OutputVote();
      }
    }
    HTML::OutputFooter(true);
  }

  //ヘッダ出力
  private static function OutputHeader() {
    switch (RQ::Get()->scene) {
    case RoomScene::BEFORE:
      $scene = GameLogMessage::BEFOREGAME;
      break;

    case RoomScene::DAY:
      $scene = sprintf(GameLogMessage::DAY, DB::$ROOM->date);
      break;

    case RoomScene::NIGHT:
      $scene = sprintf(GameLogMessage::NIGHT, DB::$ROOM->date);
      break;

    case RoomScene::AFTER:
      $scene = sprintf(GameLogMessage::AFTERGAME, DB::$ROOM->date);
      break;

    case RoomScene::HEAVEN:
      $scene = GameLogMessage::HEAVEN;
      break;
    }
    HTML::OutputHeaderTitle(GameLogMessage::HEADER . ' ' . $scene);
  }

  //能力発動ログ出力 (管理者限定)
  private static function OutputAbility() {
    if (RQ::Get()->user_no > 0 && DB::$SELF->IsDummyBoy() &&
	false === DB::$ROOM->IsOption('gm_login')) {
      DB::LoadSelf(RQ::Get()->user_no);
      DB::$SELF->live = UserLive::LIVE;
      RoleHTML::OutputAbility();
    }
  }

  //エラー処理
  private static function OutputError($str) {
    HTML::OutputResult(GameLogMessage::INPUT, GameLogMessage::INPUT . $str);
  }
}
