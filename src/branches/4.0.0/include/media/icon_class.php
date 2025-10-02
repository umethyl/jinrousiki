<?php
//-- アイコン基底クラス --//
class Icon {
  //パス取得
  public static function GetPath() {
    static $path;

    if (true === is_null($path)) {
      $path = URL::Combine(JINROU_ROOT, IconConfig::PATH, '');
    }
    return $path;
  }

  //ファイルパス取得
  public static function GetFile($file) {
    return self::GetPath() . $file;
  }

  //死亡アイコンパス取得
  public static function GetDead() {
    static $path;

    if (true === is_null($path)) {
      $path = URL::Combine(JINROU_IMG, IconConfig::$dead);
    }
    return $path;
  }

  //人狼アイコンパス取得
  public static function GetWolf() {
    static $path;

    if (true === is_null($path)) {
      $path = URL::Combine(JINROU_IMG, IconConfig::$wolf);
    }
    return $path;
  }

  //サイズ属性タグ取得
  public static function GetSize() {
    static $str;

    if (true === is_null($str)) {
      $str = ImageHTML::GenerateIconSize(IconConfig::WIDTH, IconConfig::HEIGHT);
    }
    return $str;
  }
}
