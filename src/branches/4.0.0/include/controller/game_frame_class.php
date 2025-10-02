<?php
//◆文字化け抑制◆//
//-- GameFrame コントローラー --//
final class GameFrameController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('game_frame', true);
  }

  protected static function Output() {
    GameFrameHTML::Output();
  }
}
