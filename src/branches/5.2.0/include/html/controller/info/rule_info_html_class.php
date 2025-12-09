<?php
//-- HTML 生成クラス (RuleInfo 拡張) --//
final class RuleInfoHTML {
  //出力
  public static function Output() {
    InfoHTML::Output(RuleInfoMessage::TITLE, 'rule');
  }

  //役職画像出力
  public static function OutputImage($name, $alt = null, $table = false) {
    echo ImageManager::Role()->Generate($name, $alt, $table);
  }
}
