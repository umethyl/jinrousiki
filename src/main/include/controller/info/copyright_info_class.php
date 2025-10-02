<?php
//-- 謝辞・素材出力クラス --//
class CopyrightInfo {
  //実行
  public static function Execute() {
    self::Output();
  }

  //出力
  private static function Output() {
    CopyrightInfoHTML::Output();
  }
}
