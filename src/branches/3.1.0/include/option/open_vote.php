<?php
/*
  ◆投票した票数を公表する (open_vote)
*/
class Option_open_vote extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '投票した票数を公表する';
  }

  public function GetExplain() {
    return '「権力者」などのサブ役職が分かりやすくなります';
  }
}
