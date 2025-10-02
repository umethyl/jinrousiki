<?php
//-- GameLog 出力クラス --//
class GameLog {
  static function Output() {
    //-- データ収集 --//
    DB::Connect();
    Session::Certify();

    DB::$ROOM = new Room(RQ::$get); //村情報を取得
    DB::$ROOM->log_mode = true;
    DB::$ROOM->single_log_mode = true;

    DB::$USER = new UserDataSet(RQ::$get); //ユーザ情報を取得
    DB::$SELF = DB::$USER->BySession(); //自分の情報をロード

    //エラーチェック
    if (! (DB::$SELF->IsDead() || DB::$ROOM->IsFinished())) { //死者かゲーム終了後だけ
      $title = 'ログ閲覧認証エラー';
      $body  = '：<a href="./" target="_top">トップページ</a>からログインしなおしてください';
      HTML::OutputResult($title, $title . $body);
    }

    $title = '入力データエラー';
    switch (RQ::$get->scene) {
    case 'aftergame':
    case 'heaven':
      if (! DB::$ROOM->IsFinished()) { //霊界・ゲーム終了後はゲーム終了後のみ
	HTML::OutputResult($title, $title . '：まだゲームが終了していません');
      }
      break;

    default:
      if (DB::$ROOM->date < RQ::$get->date ||
	  (DB::$ROOM->date == RQ::$get->date &&
	   (DB::$ROOM->IsDay() || DB::$ROOM->scene == RQ::$get->scene))) { //「未来」判定
	HTML::OutputResult($title, $title . '：無効なシーンです');
      }
      DB::$ROOM->last_date = DB::$ROOM->date;
      DB::$ROOM->date      = RQ::$get->date;
      DB::$ROOM->scene     = RQ::$get->scene;
      DB::$USER->SetEvent(true);
      break;
    }

    //-- ログ出力 --//
    GameHTML::OutputHeader('game_log');
    $format = '<h1>ログ閲覧 %s</h1>'."\n";
    switch (RQ::$get->scene) {
    case 'beforegame':
      $scene = '(開始前)';
      break;

    case 'day':
      $scene = DB::$ROOM->date . ' 日目 (昼)';
      break;

    case 'night':
      $scene = DB::$ROOM->date . ' 日目 (夜)';
      break;

    case 'aftergame':
      $scene = DB::$ROOM->date . ' 日目 (終了後)';
      break;

    case 'heaven':
      $scene = '(霊界)';
      break;
    }
    printf($format, $scene);

    if (RQ::$get->scene == 'heaven') {
      DB::$ROOM->heaven_mode = true; //念のためセット
      Talk::OutputHeaven();
      HTML::OutputFooter(true);
    }

    //能力発動ログを出力 (管理者限定)
    if (RQ::$get->user_no > 0 && DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOption('gm_login')) {
      DB::$SELF = DB::$USER->ByID(RQ::$get->user_no);
      DB::$SELF->live = 'live';
      RoleHTML::OutputAbility();
    }
    Talk::Output();
    if (DB::$ROOM->IsPlaying()) { //プレイ中は投票結果・遺言・死者を表示
      GameHTML::OutputAbilityAction();
      GameHTML::OutputLastWords();
      GameHTML::OutputDead();
    }
    elseif (DB::$ROOM->IsAfterGame()) {
      GameHTML::OutputLastWords(true); //遺言 (昼終了時限定)
    }
    if (DB::$ROOM->IsNight()) GameHTML::OutputVote();
    HTML::OutputFooter(true);
  }
}
