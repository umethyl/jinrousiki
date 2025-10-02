<?php
//-- DB アクセス (Session 拡張) --//
class SessionDB {
  //ユニーク判定
  public static function Exists() {
    DB::Prepare('SELECT room_no FROM user_entry WHERE session_id = ?', array(Session::GetID()));
    return DB::Exists();
  }

  //認証
  public static function Certify() {
    $query = 'SELECT user_no FROM user_entry WHERE session_id = ? AND room_no = ? AND live <> ?';
    DB::Prepare($query, array(Session::GetID(), RQ::Get()->room_no, UserLive::KICK));
    return DB::FetchColumn();
  }
}
