<?php
//-- ◆文字化け抑制◆ --//
//-- 裏・闇鍋モードテストコントローラー --//
final class ChaosVersoTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function LoadRequestExtra() {
    DevHTML::LoadRequest();
  }

  protected static function OutputRunHeader() {
    DevHTML::OutputRoleTestHeader(ChaosVersoTestMessage::TITLE, 'chaos_verso.php');
    FormHTML::OutputFooter();
  }

  protected static function EnableCommand() {
    return DevHTML::IsExecute();
  }

  protected static function RunCommand() {
    RQ::InitTestRoom();
    $stack = new stdClass();
    $stack->game_option = ['chaos_verso'];
    $stack->option_role = ['chaos_open_cast_full'];
    DevRoom::Cast($stack);
  }

  protected static function OutputRunFooter() {
    HTML::OutputFooter();
  }
}
