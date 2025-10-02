<?php
//◆文字化け抑制◆//
//-- GameFrame コントローラー --//
final class GameFrameController extends JinrouController {
  protected static function Load() {
    RQ::LoadRequest('game_frame');
  }

  protected static function Output() {
    GameFrameHTML::Output();
  }
}
