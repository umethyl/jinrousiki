<?php
//-- 天候システム出力クラス --//
class WeatherInfo {
  //実行
  public static function Execute() {
    self::Output();
  }

  //出力
  private static function Output() {
    InfoHTML::OutputHeader(WeatherInfoMessage::TITLE, 0, 'weather');
    InfoHTML::Load('weather');
    HTML::OutputFooter();
  }
}
