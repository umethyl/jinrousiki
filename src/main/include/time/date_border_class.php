<?php
//-- 日付境界判定 --//
final class DateBorder {
  //-- 固定 --//
  public static function Number($date) {
    return DB::$ROOM->IsDate($date);
  }

  //1日目
  public static function One() {
    return self::Number(1);
  }

  //-- 以降 --//
}
