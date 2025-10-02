<?php
//◆文字化け抑制◆
//-- GameUp コントローラー --//
final class GameUpController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('game_up', true);
  }

  protected static function Output() {
    GameUpHTML::Output();
  }
}
