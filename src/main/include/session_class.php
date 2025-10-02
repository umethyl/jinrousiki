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

  //セッション開始
  static function Start() {
    if (is_null(self::$id)) new self();
  }

  //データ取得
  static function Get($type, $key) {
    return $_SESSION[$type][$key];
  }

  //ID 取得
  static function GetID() {
    self::Start();
    return self::$id;
  }

  //DB ユニークな ID 取得
  static function GetUniqID() {
    self::Start();
    do {
      self::Reset();
    } while (SessionDB::Exists());
    return self::GetID();
  }

  //認証したユーザの ID 取得
  static function GetUser() {
    return self::$user_no;
  }

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
  static function Certify() {
    $stack = SessionDB::Certify();
    if (count($stack) == 1) {
      self::$user_no = array_shift($stack);
      return true;
    }
    return false;
  }

  //ログイン
  static function Login() {
    return self::Certify() ? true : self::Output();
  }

  //ログイン (game_play 専用)
  static function LoginGamePlay() {
    if (self::Certify()) return true;

    //村が存在するなら観戦ページにジャンプする
    RoomDataDB::Exists() ? self::OutputJump() : self::Output();
  }

  //ID セット
  private static function SetID() {
    return self::$id = session_id();
  }

  //エラー出力
  private static function Output() {
    $title = Message::SESSION_ERROR;
    HTML::OutputResult($title, $title . Message::TOP);
  }

  //観戦ページ移動
  private static function OutputJump() {
    $url  = sprintf('game_view.php?room_no=%d', RQ::Get()->room_no);
    $jump = sprintf(Message::JUMP, $url);
    $body = Message::VIEW_BODY . Text::BRLF . $jump . Text::LF . HTML::GenerateSetLocation();
    HTML::OutputResult(Message::VIEW_TITLE, $body, $url);
  }
}

//-- データベースアクセス (Session 拡張) --//
class SessionDB {
  //ユニーク判定
  static function Exists() {
    DB::Prepare('SELECT room_no FROM user_entry WHERE session_id = ?', array(Session::GetID()));
    return DB::Exists();
  }

  //認証
  static function Certify() {
    $query = 'SELECT user_no FROM user_entry WHERE session_id = ? AND room_no = ? AND live <> ?';
    DB::Prepare($query, array(Session::GetID(), RQ::Get()->room_no, UserLive::KICK));
    return DB::FetchColumn();
  }
}
