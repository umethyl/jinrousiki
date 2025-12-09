<?php
//-- 数字判定関連 --//
final class Number {
  //範囲内 (a < target <= b)
  public static function Within(int $number, int $from, int $to) {
    return $from < $number && $number <= $to;
  }

  //範囲内 (a < target < b)
  public static function InRange(int $number, int $from, int $to) {
    return $from < $number && $number < $to;
  }

  //範囲外 (target < a || b < target)
  public static function OutRange(int $number, int $from, int $to) {
    return $number < $from || $to < $number;
  }

  //倍数
  public static function Multiple(int $number, int $base, int $target, $limit = null) {
    if (null === $limit) {
      return $number % $base == $target;
    } else {
      return $number < $limit || $number % $base == $target;
    }
  }

  //偶数
  public static function Even(int $number, $limit = null) {
    return self::Multiple($number, 2, 0, $limit);
  }

  //奇数
  public static function Odd(int $number, $limit = null) {
    return self::Multiple($number, 2, 1, $limit);
  }

  //3の倍数
  public static function MultipleThree(int $number, $limit = null) {
    return self::Multiple($number, 3, 0, $limit);
  }

  //割合
  public static function Percent(int $number, int $base, int $digit) {
    return sprintf('%.' . $digit . 'f', $number / $base * 100);
  }
}
