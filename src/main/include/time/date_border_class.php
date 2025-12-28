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

  //3の倍数日
  public static function OnThree(int $target = 0) {
    //日付の概念上、0 は含まない
    return self::First() && Number::MultipleThree(DB::$ROOM->date, $target);
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
  public static function Future($date) {
    return DB::$ROOM->date < $date;
  }

  //-- 以下(当日 + 未来) --//
  public static function InFuture($date) {
    return DB::$ROOM->date <= $date;
  }

  //1日目未満
  public static function PreOne() {
    return self::Future(1);
  }

  //2日目未満
  public static function PreTwo() {
    return self::Future(2);
  }

  //3日目未満
  public static function PreThree() {
    return self::Future(3);
  }

  //4日目未満
  public static function PreFour() {
    return self::Future(4);
  }

  //5日目未満
  public static function PreFive() {
    return self::Future(5);
  }

  //規定日以降の偶数日
  public static function EvenFuture(int $date) {
    return self::Future($date) || Number::Even(DB::$ROOM->date);
  }
}
