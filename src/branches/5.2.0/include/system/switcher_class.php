<?php
//-- Switch (bool) 関連 --//
final class Switcher {
  const ON  = 'on';
  const OFF = 'off';
  const OK  = 'true';
  const NG  = 'false';

  /* 判定 */
  //ON・OFF 判定
  public static function IsOn($str) {
    return $str == self::ON;
  }

  /* 変換 */
  //ON・OFF 変換
  public static function Get($flag) {
    return (true === $flag) ? self::ON : self::OFF;
  }

  //OK・NG 変換
  public static function GetBool($flag) {
    return (true === $flag) ? self::OK : self::NG;
  }
}
