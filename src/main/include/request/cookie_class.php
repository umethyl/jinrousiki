<?php
//-- クッキー処理クラス --//
class JinrouCookie {
  const TIME = 3600; //保持時間
  public static $scene;		//夜明け
  public static $objection;	//「異議」あり情報
  public static $vote_result;	//投票結果
  public static $vote_count;	//投票回数
  public static $user_count;	//参加人数
  public static $objection_list = []; //最新の「異議」あり情報

  //ユーザー登録時の初期化処理
  public static function Initialize() {
    DB::$ROOM->system_time = Time::Get(); //現在時刻を取得
    $time = DB::$ROOM->system_time - self::TIME;
    foreach (['scene', 'objection', 'vote_result', 'vote_count', 'user_count'] as $key) {
      setcookie($key, '', $time);
    }
  }

  //データ設置
  public static function Set() {
    self::Load(); //データロード
    $time = DB::$ROOM->system_time + self::TIME;

    /* 夜明け */
    setcookie('scene', DB::$ROOM->scene, $time); //シーンを登録

    /* 再投票 */
    if (DB::$ROOM->vote_count > 1) {
      setcookie('vote_count', DB::$ROOM->vote_count, $time); //再投票回数を登録
    } else {
      setcookie('vote_count', '', DB::$ROOM->system_time - self::TIME); //再投票が無いなら削除
    }

    /* 入村情報 */
    if (DB::$ROOM->IsBeforeGame()) { //現在のユーザ人数を登録
      setcookie('user_count', DB::$USER->Count(), $time);
    }

    if (DB::$ROOM->IsAfterGame()) return; //ゲーム終了ならスキップ

    /* 投票済み */
    if (self::$vote_result != '') setcookie('vote_result', '', $time);

    /* 「異議」あり */
    self::$objection_list = Objection::GetCookie();
    setcookie('objection', ArrayFilter::ToCSV(self::$objection_list), $time);
  }

  //投票結果セット
  public static function SetVote($result) {
    setcookie('vote_result', $result, DB::$ROOM->system_time + self::TIME);
  }

  //データロード
  private static function Load() {
    self::$scene       = ArrayFilter::Get($_COOKIE, 'scene');
    self::$objection   = ArrayFilter::Get($_COOKIE, 'objection');
    self::$vote_result = ArrayFilter::Get($_COOKIE, 'vote_result');
    self::$vote_count  = ArrayFilter::GetInt($_COOKIE, 'vote_count');
    self::$user_count  = ArrayFilter::GetInt($_COOKIE, 'user_count');
  }
}
