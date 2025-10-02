<?php
//-- アイコン一覧表示コントローラー --//
final class IconViewController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('icon_view');
    DB::Connect();
    Session::Start();
  }

  protected static function Output() {
    IconViewHTML::Output();
  }
}
