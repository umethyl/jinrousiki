<?php
//-- GameFrame 出力クラス --//
class GameFrame {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('game_frame', true);
  }

  //出力
  private static function Output() {
    GameFrameHTML::Output();
  }
}
