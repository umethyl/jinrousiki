<?php
/*
  ◆大司祭 (high_priest)
  ○仕様
  ・司祭：司祭＆司教 (5日目以降)
*/
RoleLoader::LoadFile('priest');
class Role_high_priest extends Role_priest {
  protected function IgnoreResult() {
    return DateBorder::PreFive();
  }

  protected function GetPriestResultRole() {
    return Number::Even(DB::$ROOM->date) ? 'priest' : 'bishop_priest';
  }

  protected function IgnoreSetPriest() {
    return DateBorder::PreFour();
  }

  protected function GetPriestType() {
    return Number::Odd(DB::$ROOM->date) ? 'human_side' : 'dead';
  }

  protected function IgnorePriest() {
    return in_array($this->GetPriestRole(), $this->GetStack('priest')->list);
  }

  protected function GetPriestRole() {
    return Number::Odd(DB::$ROOM->date) ? 'priest' : 'bishop_priest';
  }
}
