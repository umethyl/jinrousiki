<?php
//◆文字化け抑制◆
//-- GameUp コントローラー --//
final class GameUpController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'game_up';
  }

  protected static function Output() {
    GameUpHTML::Output();
  }
}
