<?php
//◆文字化け抑制◆//
//-- 謝辞・素材情報コントローラー --//
final class CopyrightInfoController extends JinrouController {
  protected static function Output() {
    CopyrightInfoHTML::Output();
  }
}
