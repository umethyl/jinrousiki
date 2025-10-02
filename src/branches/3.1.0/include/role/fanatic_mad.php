<?php
/*
  ◆狂信者 (fanatic_mad)
  ○仕様
  ・仲間表示：人狼枠(憑依追跡)
*/
class Role_fanatic_mad extends Role {
  public $mix_in = array('wolf');

  protected function GetPartner() {
    $stack = $this->GetWolfPartner();
    unset($stack['mad_partner']);
    unset($stack['unconscious_list']);
    return $stack;
  }
}
