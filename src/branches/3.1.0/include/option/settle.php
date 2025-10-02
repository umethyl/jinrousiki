<?php
/*
  ◆決着村 (settle)
  ○仕様
*/
class Option_settle extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '決着村';
  }

  public function GetExplain() {
    return '処刑投票による引き分けが発生しません';
  }

  //処刑者決定
  public function DecideVoteKill() {
    $vote_kill_uname = Lottery::Get(RoleManager::Stack()->Get('vote_possible'));
    RoleManager::Stack()->Set('vote_kill_uname', $vote_kill_uname);
  }
}
