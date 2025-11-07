<?php
//◆文字化け抑制◆//
//-- 闇鍋モード情報コントローラー --//
final class ChaosInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function Output() {
    InfoHTML::OutputHeader(ChaosInfoMessage::TITLE, 0, 'chaos');
    InfoHTML::Load('chaos');
    HTML::OutputFooter();
  }
}
