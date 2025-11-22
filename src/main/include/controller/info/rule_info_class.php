<?php
//◆文字化け抑制◆//
//-- ルール情報コントローラー --//
final class RuleInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function LoadSetting() {
    Loader::LoadClass('InfoTime');
  }

  protected static function Output() {
    RuleInfoHTML::Output();
  }
}
