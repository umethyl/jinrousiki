<?php
/*
  ◆恋司祭 (priest_jealousy)
  ○仕様
  ・役職表示：司祭
  ・司祭：恋人 (偶数日 / 4日目以降)
*/
class Role_priest_jealousy extends Role {
  public $mix_in = ['priest'];
  public $display_role = 'priest';

  protected function IgnoreResult() {
    return DateBorder::OddFuture(3);
  }

  protected function OutputAddResult() {
    $this->OutputPriestResult();
  }

  protected function GetPriestType() {
    return 'lovers';
  }
}
