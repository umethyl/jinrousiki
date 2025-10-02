<?php
/*
  ◆足音村 (step)
  ○仕様
  ・配役：足音能力者
*/
class Option_step extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '足音村';
  }

  public function SetFilterRole($count) {
    $stack = [
      'step_mage' => 'mage', 'necromancer', 'step_guard' => 'guard',
      'step_wolf' => 'wolf', 'step_mad' => 'mad', 'step_fox' => 'fox'
    ];
    return Cast::FilterRole($count, $stack);
  }
}
