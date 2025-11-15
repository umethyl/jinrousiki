<?php
//-- HTML 生成クラス (GameLog 拡張) --//
final class GameLogHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    if (RQ::Fetch()->scene == RoomScene::HEAVEN) {
      self::OutputHeaven();
    } else {
      self::OutputLog();
    }
    HTML::OutputFooter(true);
  }

  //ヘッダ出力
  private static function OutputHeader() {
    GameHTML::OutputHeader('game_log');
    switch (RQ::Fetch()->scene) {
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
    HeaderHTML::OutputTitle(GameLogMessage::HEADER . ' ' . $scene);
  }

  //霊界出力
  private static function OutputHeaven() {
    DB::$ROOM->SetFlag(RoomMode::HEAVEN); //念のためセット
    Talk::OutputHeaven();
  }

  //ログ出力
  private static function OutputLog() {
    self::OutputAbility();
    Talk::Output();
    self::OutputSystem();
    self::OutputVote();
  }

  //能力発動ログ出力 (管理者限定)
  private static function OutputAbility() {
    if (RQ::Fetch()->user_no > 0 && DB::$SELF->IsDummyBoy() &&
	false === DB::$ROOM->IsOption('gm_login')) {
      DB::LoadSelf(RQ::Fetch()->user_no);
      DB::$SELF->live = UserLive::LIVE;
      RoleHTML::OutputAbility();
    }
  }

  //システムメッセージ出力
  private static function OutputSystem() {
    if (DB::$ROOM->IsPlaying()) { //プレイ中は投票結果・遺言・死者を表示
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
    } elseif (DB::$ROOM->IsAfterGame()) {
      GameHTML::OutputLastWords(true); //遺言 (昼終了時限定)
    }
  }

  //投票結果出力
  private static function OutputVote() {
    if (DB::$ROOM->IsNight()) {
      GameHTML::OutputVote();
    }
  }
}
