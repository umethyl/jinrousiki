<?php
/*
  ◆恋司祭 (priest_jealousy)
  ○仕様
  ・役職表示：司祭
  ・司祭：恋人
*/
class Role_priest_jealousy extends Role {
  public $mix_in = 'priest';
  public $display_role = 'priest';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || DB::$ROOM->date % 2 == 1;
  }

  protected function OutputAddResult() {
    $this->filter->OutputPriestResult();
  }

  public function GetPriestType() {
    return 'lovers';
  }
}
