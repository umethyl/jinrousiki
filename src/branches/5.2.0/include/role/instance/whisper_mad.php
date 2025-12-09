<?php
/*
  ◆囁き狂人 (whisper_mad)
  ○仕様
  ・仲間表示：人狼系(憑依追跡)・囁き狂人
*/
class Role_whisper_mad extends Role {
  public $mix_in = ['wolf'];

  protected function GetPartner() {
    $stack = $this->GetWolfPartner();
    unset($stack['unconscious_list']);
    return $stack;
  }
}
