<?php
//-- 裏・闇鍋モードテスト --//
class ChaosVersoTest {
  //実行
  public static function Execute() {
    self::Output();
  }

  //出力
  private static function Output() {
    DevHTML::OutputRoleTestHeader(ChaosVersoTestMessage::TITLE, 'chaos_verso.php');
    HTML::OutputFormFooter();
    if (DevHTML::IsExecute()) self::RunTest();
    HTML::OutputFooter(true);
  }

  //テスト実行
  private static function RunTest() {
    RQ::InitTestRoom();
    $stack = new stdClass();
    $stack->game_option = array('chaos_verso');
    $stack->option_role = array();
    DevRoom::Cast($stack);
  }
}
