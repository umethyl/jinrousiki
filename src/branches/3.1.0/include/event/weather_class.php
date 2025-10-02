<?php
//-- 天候データマネージャ --//
class WeatherManager {
  //天候データ取得
  public static function Get($id = null) {
    return WeatherData::$list[$id];
  }

  //イベント取得
  public static function GetEvent($id) {
    return WeatherData::$list[$id]['event'];
  }

  //存在判定
  public static function Exists($id) {
    return isset(WeatherData::$list[$id]);
  }

  //天候名出力
  public static function OutputName($id) {
    echo WeatherData::$list[$id]['name'];
  }

  //説明出力
  public static function OutputCaption($id) {
    echo WeatherData::$list[$id]['caption'];
  }
}
