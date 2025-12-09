<?php
//-- 日時関連 --//
final class Time {
  //TZ 補正をかけた時刻を返す (環境変数 TZ を変更できない環境想定？)
  public static function Get() {
    $time = time();
    if (ServerConfig::ADJUST_TIME) {
      $time += ServerConfig::OFFSET_SECONDS;
    }
    return $time;
    /*
    // ミリ秒対応のコード(案) 2009-08-08 enogu
    $preg = '/([0-9]+)( [0-9]+)?/i';
    return preg_replace($preg, '$$2.$$1', microtime()) + ServerConfig::OFFSET_SECONDS; // ミリ秒
    */
  }

  //TZ 補正をかけた日時を返す
  public static function GetDate($format, $time) {
    return ServerConfig::ADJUST_TIME ? gmdate($format, $time) : date($format, $time);
  }

  //DATETIME 形式の日時を返す
  public static function GetDateTime($time) {
    return self::GetDate('Y-m-d H:i:s', $time);
  }

  //TIMESPAMP 形式の日時を返す
  public static function GetTimeStamp($time) {
    return self::GetDate('Y/m/d (D) H:i:s', $time);
  }

  //分 -> 秒
  public static function ByMinute($minute) {
    return $minute * 60;
  }

  //時間 -> 秒
  public static function ByHour($hour) {
    return self::ByMinute($hour * 60);
  }

  //時間 (秒) を変換する
  public static function Convert($second) {
    $hour   = 0;
    $minute = 0;
    if ($second >= 60) {
      $minute = floor($second / 60);
      $second %= 60;
    }
    if ($minute >= 60) {
      $hour = floor($minute / 60);
      $minute %= 60;
    }

    $str = '';
    if ($hour > 0) {
      $str .= $hour . Message::HOUR;
    }
    if ($minute > 0) {
      $str .= $minute . Message::MINUTE;
    }
    if ($second > 0) {
      $str .= $second . Message::SECOND;
    }
    return $str;
  }

  //TIMESTAMP 形式の時刻を変換する
  public static function ConvertTimeStamp($time_stamp, $date = true) {
    $time = strtotime($time_stamp);
    if (ServerConfig::ADJUST_TIME) {
      $time += ServerConfig::OFFSET_SECONDS;
    }
    return $date ? self::GetTimeStamp($time) : $time;
  }
}
