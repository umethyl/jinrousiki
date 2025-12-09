<?php
//-- ◆文字化け抑制◆ --//
//-- データベース初期セットアップコントローラー --//
final class JinrouAdminSetupController extends JinrouController {
  protected static function IsAdmin() {
    return true;
  }

  protected static function GetAdminType() {
    return 'setup';
  }

  protected static function EnableLoadRequest() {
    return false;
  }

  protected static function Output() {
    JinrouSetupManager::Execute();
  }
}
