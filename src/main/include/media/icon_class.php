<?php
//-- アイコン基底クラス --//
class Icon {
  //パス取得
  public static function GetPath() {
    static $path;

    if (is_null($path)) {
      $path = JINROU_ROOT . '/' . IconConfig::PATH . '/';
    }
    return $path;
  }

  //ファイルパス取得
  public static function GetFile($file) {
    return self::GetPath() . $file;
  }

  //死亡アイコン取得
  public static function GetDead() {
    static $path;

    if (is_null($path)) {
      $path = JINROU_IMG . '/' . IconConfig::$dead;
    }
    return $path;
  }

  //人狼アイコン取得
  public static function GetWolf() {
    static $path;

    if (is_null($path)) {
      $path = JINROU_IMG . '/' . IconConfig::$wolf;
    }
    return $path;
  }

  //サイズ属性タグ取得
  public static function GetTag() {
    static $str;

    if (is_null($str)) {
      $str = ImageHTML::GenerateIconSize(IconConfig::WIDTH, IconConfig::HEIGHT);
    }
    return $str;
  }
}
