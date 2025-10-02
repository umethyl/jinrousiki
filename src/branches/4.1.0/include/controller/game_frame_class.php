<?php
//◆文字化け抑制◆//
//-- GameFrame コントローラー --//
final class GameFrameController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'game_frame';
  }

  protected static function Output() {
    GameFrameHTML::Output();
  }
}
