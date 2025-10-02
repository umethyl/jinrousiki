<?php
//-- GameUp 出力クラス --//
class GameUp {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('game_up', true);
  }

  //出力
  private static function Output() {
    GameUpHTML::Output();
  }
}
