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
    //$ip_address = $_SERVER['REMOTE_ADDR']; //IPアドレス認証は現在は行っていない
    //セッション ID による認証
    $query = "SELECT user_no FROM user_entry WHERE room_no = %d AND session_id = '%s'" .
      " AND live <> 'kick'";
    $stack = DB::FetchArray(sprintf($query, RQ::$get->room_no, self::GetID()));
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

    //村が存在するなら観戦ページにジャンプする
    $query = 'SELECT room_no FROM room WHERE room_no = %d';
    if (DB::Count(sprintf($query, RQ::$get->room_no)) > 0) {
      $url   = sprintf('game_view.php?room_no=%d', RQ::$get->room_no);
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
  private function SetID() {
    return self::$id = session_id();
  }

  //DB に登録されているセッション ID と被らないようにする
  private function GetUniq() {
    $query = "SELECT room_no FROM user_entry WHERE session_id = '%s'";
    do {
      self::Reset();
    } while (DB::Count(sprintf($query, self::GetID())) > 0);
    return self::GetID();
  }

  //エラー出力
  private function Output() {
    $title = 'セッション認証エラー';
    $body  = $title . '：<a href="./" target="_top">トップページ</a>からログインしなおしてください';
    HTML::OutputResult($title, $body);
  }
}
