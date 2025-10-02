<?php
//◆文字化け抑制◆//
//-- 天候システム情報コントローラー --//
final class WeatherInfoController extends JinrouController {
  protected static function Output() {
    InfoHTML::OutputHeader(WeatherInfoMessage::TITLE, 0, 'weather');
    InfoHTML::Load('weather');
    HTML::OutputFooter();
  }
}
