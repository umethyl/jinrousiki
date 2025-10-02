<?php
//-- アイコン基底クラス --//
class Icon {
  private static $path = null;
  private static $dead = null;
  private static $wolf = null;
  private static $tag  = null;

  //パス取得
  static function GetPath() {
    if (is_null(self::$path)) self::$path = JINROU_ROOT . '/' . IconConfig::PATH . '/';
    return self::$path;
  }

  //ファイルパス取得
  static function GetFile($file) { return self::GetPath() . $file; }

  //死亡アイコン取得
  static function GetDead() {
    if (is_null(self::$dead)) self::$dead = JINROU_IMG . '/' . IconConfig::$dead;
    return self::$dead;
  }

  //人狼アイコン取得
  static function GetWolf() {
    if (is_null(self::$wolf)) self::$wolf = JINROU_IMG . '/' . IconConfig::$wolf;
    return self::$wolf;
  }

  //タグ取得
  static function GetTag() {
    if (is_null(self::$tag)) {
      self::$tag = sprintf('width="%d" height="%d"', IconConfig::WIDTH, IconConfig::HEIGHT);
    }
    return self::$tag;
  }

  //ユーザアイコンタグ取得
  static function GetUserIcon(User $user) {
    $format = '<img src="%s" style="border-color: %s;" alt="" align="middle" %s>';
    return sprintf($format, self::GetFile($user->icon_filename), $user->color, self::GetTag());
  }
}
