<?php
//-- 詳細な仕様出力クラス --//
class SpecInfo {
  //実行
  public static function Execute() {
    self::Output();
  }

  //出力
  private static function Output() {
    InfoHTML::OutputHeader(SpecInfoMessage::TITLE, 0, 'spec');
    InfoHTML::Load('spec');
    HTML::OutputFooter();
  }
}
