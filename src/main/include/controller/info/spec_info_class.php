<?php
//◆文字化け抑制◆//
//-- 詳細な仕様情報コントローラー --//
final class SpecInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function Output() {
    InfoHTML::OutputHeader(SpecInfoMessage::TITLE, 0, 'spec');
    InfoHTML::Load('spec');
    HTML::OutputFooter();
  }
}
