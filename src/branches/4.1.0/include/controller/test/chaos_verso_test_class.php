<?php
//-- 裏・闇鍋モードテストコントローラー --//
final class ChaosVersoTestController extends JinrouController {
  protected static function Output() {
    DevHTML::OutputRoleTestHeader(ChaosVersoTestMessage::TITLE, 'chaos_verso.php');
    HTML::OutputFormFooter();
    if (DevHTML::IsExecute()) {
      self::RunTest();
    }
    HTML::OutputFooter(true);
  }

  //テスト実行
  private static function RunTest() {
    RQ::InitTestRoom();
    $stack = new stdClass();
    $stack->game_option = ['chaos_verso'];
    $stack->option_role = [];
    DevRoom::Cast($stack);
  }
}
