<?php
/*
  ◆元神話マニア (copied)
  ○仕様
  ・結果表示：2日目
*/
class Role_copied extends Role {
  public $result = 'MANIA_RESULT';
  public $display_date = 2;

  protected function OutputImage() { return; }

  protected function OutputResult() {
    if (DB::$ROOM->IsDate($this->display_date)) $this->OutputAbilityResult($this->result);
  }
}
