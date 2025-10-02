<?php
//--◆ DB アクセス (Login 拡張) ◆--//
class LoginDB {
  //ユーザ認証
  public static function Certify($uname, $password) {
    self::Prepare(DB::SetSelect('user_entry', 'user_no'), $uname, $password);
    return DB::Count() == 1;
  }

  //セッション ID 再登録
  public static function Update($uname, $password) {
    self::Prepare('UPDATE user_entry SET session_id = ?', $uname, $password, true);
    return DB::Execute();
  }

  //Prepare 処理
  private static function Prepare($query, $uname, $password, $update = false) {
    $query .= ' WHERE room_no = ? AND uname = ? AND password = ? AND live <> ?';
    $list   = array(RQ::Get()->room_no, $uname, $password, UserLive::KICK);
    if ($update) array_unshift($list, Session::GetUniqID());
    DB::Prepare($query, $list);
  }
}
