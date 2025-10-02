<?php
//-- セッション管理クラス --//
class Session {
  private static $id      = null;
  private static $user_no = null;

  //初期化
  private function __construct() {
    session_start();
    return self::SetID();
  }

  //セッションスタート
  static function Start() {
    if (is_null(self::$id)) new self();
  }

  //データ取得
  static function Get($type, $key) { return $_SESSION[$type][$key]; }

  //ID 取得
  static function GetID($uniq = false) {
    self::Start();
    return $uniq ? self::GetUniq() : self::$id;
  }

  //認証したユーザの ID 取得
  static function GetUser() { return self::$user_no; }

  //データセット
  static function Set($type, $key, $value) {
    $_SESSION[$type][$key] = $value;
  }

  //データ削除
  static function Clear($type) {
    unset($_SESSION[$type]);
  }

  //ID リセット
  static function Reset() {
    self::Start();
    session_regenerate_id();
    return self::SetID();
  }

  //認証
  static function Certify($exit = true) {
    $stack = SessionDB::Certify();
    if (count($stack) == 1) {
      self::$user_no = array_shift($stack);
      return true;
    }

    if ($exit) self::Output(); //エラー処理
    return false;
  }

  //認証 (game_play 専用)
  static function CertifyGamePlay() {
    if (self::Certify(false)) return true;

    if (RoomDataDB::Exists()) { //村が存在するなら観戦ページにジャンプする
      $url   = sprintf('game_view.php?room_no=%d', RQ::Get()->room_no);
      $title = '観戦ページにジャンプ';
      $body  = "観戦ページに移動します。<br>\n" .
	'切り替わらないなら <a href="%s" target="_top">ここ</a> 。' . "\n" . '%s';
      HTML::OutputResult($title, sprintf($body, $url, HTML::GenerateSetLocation()), $url);
    }
    else {
      self::Output();
    }
  }

  //ID セット
  private static function SetID() {
    return self::$id = session_id();
  }

  //DB に登録されているセッション ID と被らないようにする
  private static function GetUniq() {
    do {
      self::Reset();
    } while (SessionDB::Exists());
    return self::GetID();
  }

  //エラー出力
  private static function Output() {
    $title = 'セッション認証エラー';
    $body  = $title . '：<a href="./" target="_top">トップページ</a>からログインしなおしてください';
    HTML::OutputResult($title, $body);
  }
}

//-- データベースアクセス (Session 拡張) --//
class SessionDB {
  //ユニーク判定
  static function Exists() {
    $query = 'SELECT room_no FROM user_entry WHERE session_id = ?';
    DB::Prepare($query, array(Session::GetID()));
    return DB::Count() > 0;
  }

  //認証
  static function Certify() {
    $query = 'SELECT user_no FROM user_entry WHERE session_id = ? AND room_no = ? AND live <> ?';
    DB::Prepare($query, array(Session::GetID(), RQ::Get()->room_no, 'kick'));
    return DB::FetchColumn();
  }
}
