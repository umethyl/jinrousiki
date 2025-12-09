<?php
//-- ◆文字化け抑制◆ --//
//--  ログ削除(管理用)コントローラー --//
final class JinrouAdminLogDeleteController extends JinrouController {
  protected static function IsAdmin() {
    return true;
  }

  protected static function GetAdminType() {
    return 'log_delete';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function Output() {
    JinrouLogDeleteManager::Execute();
  }
}
