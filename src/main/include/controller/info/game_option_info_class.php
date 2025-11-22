<?php
//◆文字化け抑制◆//
//-- ゲームオプション情報コントローラー --//
final class GameOptionInfoController extends JinrouController {
  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function Output() {
    GameOptionInfoHTML::Output();
  }
}
