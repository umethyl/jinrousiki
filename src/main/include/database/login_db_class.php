<?php
//--◆ DB アクセス (Login 拡張) ◆--//
final class LoginDB {
  //Login 実行処理
  public static function Execute($uname, $password) {
    $list = [RQ::Get()->room_no, $uname, $password, UserLive::KICK];
    return self::Certify($list) && self::Update($list);
  }

  //ユーザ認証
  private static function Certify(array $list) {
    $query = self::GetQuery()->Select(['user_no']);

    DB::Prepare($query->Build(), $list);
    return DB::Count() == 1;
  }

  //セッション ID 再登録
  private static function Update(array $list) {
    $query = self::GetQuery()->Update()->Set(['session_id']);
    array_unshift($list, Session::GetUniqID());

    DB::Prepare($query->Build(), $list);
    return DB::Execute();
  }

  //共通 Query 取得
  private static function GetQuery() {
    return Query::Init()->Table('user_entry')
      ->Where(['room_no', 'uname', 'password'])->WhereNot('live');
  }
}
