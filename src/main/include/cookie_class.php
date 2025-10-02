<?php
//-- クッキー処理クラス --//
class JinroCookie {
  const TIME = 3600; //保持時間
  static $scene;      //夜明け
  static $objection;  //「異議あり」情報
  static $vote_count; //投票回数
  static $user_count; //参加人数
  static $objection_list = array(); //最新の「異議あり」情報

  //データ設置
  static function Set() {
    self::Load(); //データロード
    $time = DB::$ROOM->system_time + self::TIME;

    /* 夜明け */
    setcookie('scene', DB::$ROOM->scene, $time); //シーンを登録

    /* 再投票 */
    if (DB::$ROOM->vote_count > 1) { //再投票回数を登録
      setcookie('vote_count', DB::$ROOM->vote_count, $time);
    }
    else { //再投票が無いなら削除
      setcookie('vote_count', '', DB::$ROOM->system_time - self::TIME);
    }

    /* 入村情報 */
    if (DB::$ROOM->IsBeforeGame()) { //現在のユーザ人数を登録
      setcookie('user_count', DB::$USER->GetUserCount(), $time);
    }

    /* 「異議」あり */
    if (DB::$ROOM->IsAfterGame()) return; //ゲーム終了ならスキップ
    $user_count = DB::$USER->GetUserCount(true); //KICK も含めたユーザ総数を取得
    $objection_list = array_fill(0, $user_count, 0); //配列をセット (index は 0 から)

    //ユーザ全体の「異議」ありを集計
    $count = 0;
    foreach (DB::$USER->name as $uname => $id) {
      $objection_list[$count++] = DB::$USER->ByID($id)->objection;
    }

    //リストを登録
    setcookie('objection', implode(',', $objection_list), $time);
    self::$objection_list = $objection_list;
  }

  //データロード
  private function Load() {
    self::$scene      = @$_COOKIE['scene'];
    self::$objection  = @$_COOKIE['objection'];
    self::$vote_count = @(int)$_COOKIE['vote_count'];
    self::$user_count = @(int)$_COOKIE['user_count'];
  }
}
