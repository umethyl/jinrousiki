<?php
//-- 配役一覧情報コントローラー --//
final class CastInfoController extends JinrouController {
  protected static function Output() {
    InfoHTML::OutputHeader(CastInfoMessage::TITLE, 0, 'cast');
    InfoHTML::OutputCast();
    HTML::OutputFooter();
  }
}
