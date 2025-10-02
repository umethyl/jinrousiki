<?php
//-- ルール出力クラス --//
class RuleInfo {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadClass('InfoTime');
  }

  //出力
  private static function Output() {
    InfoHTML::OutputHeader(RuleInfoMessage::TITLE, 0, 'rule');
    InfoHTML::Load('rule');
    HTML::OutputFooter();
  }

  //役職画像出力
  public static function OutputImage($name, $alt = null, $table = false) {
    echo ImageManager::Role()->Generate($name, $alt, $table);
  }
}
