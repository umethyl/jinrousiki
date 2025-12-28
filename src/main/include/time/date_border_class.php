<?php
//-- 日付境界判定 --//
final class DateBorder {
  //-- 固定(当日) --//
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

  //-- 以降(過去) --//
  public static function Past($date) {
    return DB::$ROOM->date > $date;
  }

  //1日目以降
  public static function First() {
    return self::Past(0);
  }

  //2日目以降
  public static function Second() {
    return self::Past(1);
  }

  //3日目以降
  public static function Third() {
    return self::Past(2);
  }

  //4日目以降
  public static function Fourth() {
    return self::Past(3);
  }

  //5日目以降
  public static function Fifth() {
    return self::Past(4);
  }

  //-- 未満(未来) --//
  public static function Lower($date) {
    return DB::$ROOM->date < $date;
  }

  //-- 以下(当日 + 未来) --//
  public static function InLower($date) {
    return DB::$ROOM->date <= $date;
  }

  //1日目未満
  public static function PreOne() {
    return self::Lower(1);
  }

  //2日目未満
  public static function PreTwo() {
    return self::Lower(2);
  }

  //3日目未満
  public static function PreThree() {
    return self::Lower(3);
  }

  //4日目未満
  public static function PreFour() {
    return self::Lower(4);
  }

  //5日目未満
  public static function PreFive() {
    return self::Lower(5);
  }
}
