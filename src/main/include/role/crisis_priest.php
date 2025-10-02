<?php
/*
  ◆預言者 (crisis_priest)
  ○仕様
  ・役職表示：村人
  ・司祭：人外勝利前日情報 (2日目以降)
*/
RoleLoader::LoadFile('priest');
class Role_crisis_priest extends Role_priest {
  public $display_role = 'human';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  protected function IgnoreSetPriest() {
    return false;
  }

  protected function PriestAction() {
    $data = $this->GetStack('priest');
    if (isset($data->crisis)) {
      DB::$ROOM->ResultAbility($this->GetPriestResultType(), $data->crisis);
    }
  }
}
