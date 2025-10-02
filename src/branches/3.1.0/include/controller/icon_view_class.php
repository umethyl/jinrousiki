<?php
//-- アイコン表示出力クラス --//
class IconView {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadRequest('icon_view');
    DB::Connect();
    Session::Start();
  }

  //出力
  private static function Output() {
    IconViewHTML::Output();
  }
}
