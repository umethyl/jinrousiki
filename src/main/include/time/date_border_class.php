<?php
//-- 日付境界判定 --//
final class DateBorder {
  public static function First() {
    return DB::$ROOM->IsDate(1);
  }
}
