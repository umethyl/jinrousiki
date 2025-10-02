<?php
/*
  ◆庇護者 (protected)
  ○仕様
  ・人狼襲撃耐性：身代わり (庇護者付加者)
*/
class Role_protected extends Role {
  function WolfEatResist() {
    if ($this->IgnoreSacrifice()) return false;
    $stack = array();
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      if (DB::$USER->ByID($id)->IsLive(true)) $stack[] = $id;
    }
    return $this->Sacrifice($stack);
  }

  //身代わり無効判定
  function IgnoreSacrifice() { return DB::$ROOM->IsEvent('no_sacrifice'); }

  //身代わり処理
  function Sacrifice(array $stack) {
    //Text::p($stack, sprintf('Sacrifice [%s]', $this->role));
    if (count($stack) < 1) return false;
    DB::$USER->Kill(Lottery::Get($stack), 'SACRIFICE');
    return true;
  }

  //人狼襲撃得票カウンター (Mixin 用)
  function WolfEatReaction() {
    if ($this->IgnoreSacrifice()) return false;
    $stack = array();
    $class = $this->GetClass($method = 'IsSacrifice');
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive(true) && $class->$method($user)) $stack[] = $user->id;
    }
    return $this->Sacrifice($stack);
  }
}
