<?php
//-- ◆文字化け抑制◆ --//
//-- 裏・闇鍋モードテストコントローラー --//
final class ChaosVersoTestController extends JinrouTestController {
  protected static function LoadRequestExtra() {
    RQ::Fetch()->ParsePostOn('execute');
  }

  protected static function OutputHeader() {
    DevHTML::OutputRoleTestHeader(ChaosVersoTestMessage::TITLE, 'chaos_verso.php');
    FormHTML::OutputFooter();
  }

  protected static function IsExecute() {
    return DevHTML::IsExecute();
  }

  protected static function RunTest() {
    RQ::InitTestRoom();
    $stack = new stdClass();
    $stack->game_option = ['chaos_verso'];
    $stack->option_role = ['chaos_open_cast_full'];
    DevRoom::Cast($stack);
  }
}
