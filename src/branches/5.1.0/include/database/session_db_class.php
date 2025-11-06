<?php
//-- DB アクセス (Session 拡張) --//
final class SessionDB {
  //ユニーク判定
  public static function Exists() {
    $query = self::GetQuery()->Select(['room_no']);

    DB::Prepare($query->Build(), [Session::GetID()]);
    return DB::Exists();
  }

  //認証
  public static function Certify() {
    $query = self::GetQuery()->Select(['user_no'])->Where(['room_no'])->WhereNot('live');

    DB::Prepare($query->Build(), [Session::GetID(), RQ::Get(RequestDataGame::ID), UserLive::KICK]);
    return DB::FetchColumn();
  }

  //共通 Query 取得
  private static function GetQuery() {
    return Query::Init()->Table('user_entry')->Where(['session_id']);
  }
}
