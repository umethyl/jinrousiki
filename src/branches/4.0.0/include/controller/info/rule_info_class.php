<?php
//-- ルール情報コントローラー --//
final class RuleInfoController extends JinrouController {
  protected static function Load() {
    Loader::LoadClass('InfoTime');
  }

  protected static function Output() {
    InfoHTML::OutputHeader(RuleInfoMessage::TITLE, 0, 'rule');
    InfoHTML::Load('rule');
    HTML::OutputFooter();
  }

  //役職画像出力
  public static function OutputImage($name, $alt = null, $table = false) {
    echo ImageManager::Role()->Generate($name, $alt, $table);
  }
}
