<?php
//◆文字化け抑制◆//
//-- 特徴と仕様情報コントローラー --//
final class ScriptInfoInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function LoadSetting() {
    Loader::LoadClass('InfoTime');
  }

  protected static function Output() {
    ScriptInfoHTML::Output();
  }
}
