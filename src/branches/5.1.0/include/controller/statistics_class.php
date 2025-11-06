<?php
//◆文字化け抑制◆//
//-- 統計情報コントローラー --//
final class StatisticsController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'statistics';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function GetLoadDatabaseID() {
    return RQ::Get(RequestDataGame::DB);
  }

  protected static function Output() {
    StatisticsHTML::Output();
  }
}
