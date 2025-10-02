<?php
//-- アイコン一覧表示コントローラー --//
final class IconViewController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'icon_view';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
    Session::Start();
  }

  protected static function Output() {
    IconViewHTML::Output();
  }
}
