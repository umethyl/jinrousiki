<?php
//◆文字化け抑制◆//
//-- 決闘村情報コントローラー --//
final class DuelInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function Output() {
    InfoHTML::OutputHeader(DuelInfoMessage::TITLE, 0, 'duel');
    InfoHTML::Load('duel');
    HTML::OutputFooter();
  }
}
