<?php
//-- GameLog 出力クラス --//
class GameLog {
  //実行
  static function Execute() {
    self::Load();
    self::Certify();
    self::Check();
    self::Output();
  }

  //データロード
  private static function Load() {
    DB::Connect();
    Session::Login();

    DB::LoadRoom();
    DB::$ROOM->SetFlag('log');
    DB::LoadUser();
    DB::LoadSelf();
  }

  //認証
  private static function Certify() {
    if (DB::$SELF->IsDead() || DB::$ROOM->IsFinished()) return; //死者かゲーム終了後だけ
    HTML::OutputResult(GameLogMessage::CERTIFY, GameLogMessage::CERTIFY . Message::TOP);
  }

  //シーンチェック
  private static function Check() {
    switch (RQ::Get()->scene) {
    case RoomScene::AFTER:
    case RoomScene::HEAVEN:
      if (! DB::$ROOM->IsFinished()) { //霊界・ゲーム終了後はゲーム終了後のみ
	self::OutputResult(GameLogMessage::PLAYING);
      }
      break;

    default:
      if (DB::$ROOM->date < RQ::Get()->date ||
	  (DB::$ROOM->IsDate(RQ::Get()->date) &&
	   (DB::$ROOM->IsDay() || DB::$ROOM->scene == RQ::Get()->scene))) { //未来判定
	self::OutputResult(GameLogMessage::FUTURE);
      }
      DB::$ROOM->last_date = DB::$ROOM->date;
      DB::$ROOM->date      = RQ::Get()->date;
      DB::$ROOM->SetScene(RQ::Get()->scene);
      DB::$USER->SetEvent(true);
      break;
    }
  }

  //ログ出力
  private static function Output() {
    GameHTML::OutputHeader('game_log');
    self::OutputHeader();

    if (RQ::Get()->scene == RoomScene::HEAVEN) {
      DB::$ROOM->SetFlag('heaven'); //念のためセット
      Talk::OutputHeaven();
      HTML::OutputFooter(true);
    }

    //能力発動ログを出力 (管理者限定)
    if (RQ::Get()->user_no > 0 && DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOption('gm_login')) {
      Loader::LoadFile('image_class');
      DB::LoadSelf(RQ::Get()->user_no);
      DB::$SELF->live = UserLive::LIVE;
      RoleHTML::OutputAbility();
    }

    Talk::Output();
    if (DB::$ROOM->IsPlaying()) { //プレイ中は投票結果・遺言・死者を表示
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
    }
    elseif (DB::$ROOM->IsAfterGame()) {
      GameHTML::OutputLastWords(true); //遺言 (昼終了時限定)
    }

    if (DB::$ROOM->IsNight()) GameHTML::OutputVote();
    HTML::OutputFooter(true);
  }

  //ヘッダー出力
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
    printf('<h1>%s %s</h1>' . Text::LF, GameLogMessage::HEADER, $scene);
  }

  //エラー処理
  private static function OutputResult($str) {
    HTML::OutputResult(GameLogMessage::INPUT, GameLogMessage::INPUT . $str);
  }
}
