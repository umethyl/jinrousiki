<?php
//-- 裏・闇鍋モードテストコントローラー --//
final class ChaosVersoTestController extends JinrouTestController {
  protected static function OutputHeader() {
    DevHTML::OutputRoleTestHeader(ChaosVersoTestMessage::TITLE, 'chaos_verso.php');
    HTML::OutputFormFooter();
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
