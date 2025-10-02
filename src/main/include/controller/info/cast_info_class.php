<?php
//-- 配役一覧出力クラス --//
class CastInfo {
  //実行
  public static function Execute() {
    self::Output();
  }

  //出力
  private static function Output() {
    InfoHTML::OutputHeader(CastInfoMessage::TITLE, 0, 'cast');
    InfoHTML::OutputCast();
    HTML::OutputFooter();
  }
}
