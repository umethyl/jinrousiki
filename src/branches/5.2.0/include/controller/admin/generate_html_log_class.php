<?php
//--  ログのHTML化(管理用)コントローラー --//
final class JinrouAdminGenerateHTMLLogController extends JinrouController {
  protected static function IsAdmin() {
    return true;
  }

  protected static function GetAdminType() {
    return 'generate_html_log';
  }

  protected static function GetLoadRequest() {
    return 'old_log';
  }

  protected static function LoadRequestExtra() {
    JinrouHTMLLogGenerator::Load();
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function GetLoadDatabaseID() {
    return RQ::Get(RequestDataGame::DB);
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
    JinrouHTMLLogGenerator::Execute();
  }
}
