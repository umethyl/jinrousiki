<?php
//-- Info 情報生成クラス --//
final class Info {
  //リアルタイム制のアイコン出力
  public static function OutputRealTime() {
    $format = 'リアルタイム制　昼：%d分　夜： %d分';
    $str = sprintf($format, TimeConfig::DEFAULT_DAY,  TimeConfig::DEFAULT_NIGHT);
    echo ImageManager::Room()->Generate('real_time', $str);
  }
}

//-- 日時関連 (Info 拡張) --//
final class InfoTime {
  public static $spend_day;     //非リアルタイム制の発言で消費される時間 (昼)
  public static $spend_night;   //非リアルタイム制の発言で消費される時間 (夜)
  public static $silence_day;   //非リアルタイム制の沈黙で経過する時間 (昼)
  public static $silence_night; //非リアルタイム制の沈黙で経過する時間 (夜)
  public static $silence;       //非リアルタイム制の沈黙になるまでの時間
  public static $sudden_death;  //制限時間を消費後に突然死するまでの時間

  public function __construct() {
    $day_seconds   = floor(Time::ByHour(12) / TimeConfig::DAY);
    $night_seconds = floor(Time::ByHour( 6) / TimeConfig::NIGHT);

    self::$spend_day     = Time::Convert($day_seconds);
    self::$spend_night   = Time::Convert($night_seconds);
    self::$silence_day   = Time::Convert(TimeConfig::SILENCE_PASS * $day_seconds);
    self::$silence_night = Time::Convert(TimeConfig::SILENCE_PASS * $night_seconds);
    self::$silence       = Time::Convert(TimeConfig::SILENCE);
    self::$sudden_death  = Time::Convert(TimeConfig::SUDDEN_DEATH);
  }

  //変換結果を出力する
  public static function Output($second) {
    echo Time::Convert($second);
  }
}
