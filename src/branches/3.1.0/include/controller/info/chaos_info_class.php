<?php
//-- 闇鍋モード出力クラス --//
class ChaosInfo {
  //実行
  public static function Execute() {
    self::Output();
  }

  //出力
  private static function Output() {
    InfoHTML::OutputHeader(ChaosInfoMessage::TITLE, 0, 'chaos');
    InfoHTML::Load('chaos');
    HTML::OutputFooter();
  }
}
