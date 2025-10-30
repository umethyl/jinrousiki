<?php
//-- セッション管理クラス --//
final class Session {
  private static $id      = null;
  private static $user_no = null;

  //初期化
  private function __construct() {
    session_start();
    return self::SetID();
  }

  //セッション開始
  public static function Start() {
    if (null === self::$id) {
      new self();
    }
  }

  //データ取得
  public static function Get($type, $key) {
    return $_SESSION[$type][$key];
  }

  //ID 取得
  public static function GetID() {
    self::Start();
    return self::$id;
  }

  //DB ユニークな ID 取得
  public static function GetUniqID() {
    self::Start();
    do {
      self::Reset();
    } while (SessionDB::Exists());
    return self::GetID();
  }

  //認証したユーザの ID 取得
  public static function GetUser() {
    return self::$user_no;
  }

  //データ初期化
  public static function Init($type, $key, $value = []) {
    if (false === self::Exists($type, $key)) {
      $_SESSION[$type][$key] = $value;
    }
  }

  //データ存在確認
  public static function Exists($type, $key) {
    return ArrayFilter::IsAssocKey($_SESSION, $type, $key);
  }

  //データセット
  public static function Set($type, $key, $value) {
    $_SESSION[$type][$key] = $value;
  }

  //データ削除
  public static function Clear($type) {
    unset($_SESSION[$type]);
  }

  //ID リセット
  public static function Reset() {
    self::Start();
    session_regenerate_id();
    return self::SetID();
  }

  //認証
  public static function Certify() {
    $stack = SessionDB::Certify();
    if (count($stack) == 1) {
      self::$user_no = array_shift($stack);
      return true;
    }
    return false;
  }

  //ログイン
  public static function Login() {
    return self::Certify() ? true : self::Output();
  }

  //ログイン (game_play 専用)
  public static function LoginGamePlay() {
    if (self::Certify()) {
      return true;
    }

    //村が存在するなら観戦ページにジャンプする
    RoomLoaderDB::Exists() ? self::OutputJump() : self::Output();
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
    $url  = URL::GetRoom('game_view', RQ::Fetch()->room_no);
    $body = Text::Join(Message::VIEW_BODY, URL::GetJump($url));
    $str  = Text::LineFeed($body) . HTML::GenerateSetLocation();
    HTML::OutputResult(Message::VIEW_TITLE, $str, $url);
  }
}
