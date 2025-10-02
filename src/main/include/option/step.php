<?php
/*
  ◆足音村 (step)
  ○仕様
  ・配役フィルタリング：足音能力者
*/
class Option_step extends OptionCastCheckbox {
  public function GetCaption() {
    return '足音村';
  }

  protected function GetFilterCastRoleList() {
    $stack = [
      'step_mage' => 'mage', 'necromancer', 'step_guard' => 'guard',
      'step_wolf' => 'wolf', 'step_mad' => 'mad', 'step_fox' => 'fox'
    ];
    return $stack;
  }
}
