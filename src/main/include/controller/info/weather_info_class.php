<?php
//◆文字化け抑制◆//
//-- 天候システム情報コントローラー --//
final class WeatherInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function Output() {
    InfoHTML::Output(WeatherInfoMessage::TITLE, 'weather');
  }
}
