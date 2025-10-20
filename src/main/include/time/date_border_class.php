<?php
//-- 日付境界判定 --//
final class DateBorder {
  //-- 固定 --//
  public static function On($date) {
    return DB::$ROOM->IsDate($date);
  }

  //1日目
  public static function One() {
    return self::On(1);
  }

  //2日目
  public static function Two() {
    return self::On(2);
  }

  //-- 以降 --//
}
