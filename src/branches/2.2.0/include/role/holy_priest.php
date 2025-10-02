<?php
/*
  ◆聖徳道士 (holy_priest)
  ○仕様
  ・司祭：身代わり君 + 自分 + 自分周辺の勝利陣営数 (5日目)
*/
RoleManager::LoadFile('priest');
class Role_holy_priest extends Role_priest {
  protected function GetOutputRole() {
    return DB::$ROOM->IsDate(5) ? $this->role : null;
  }

  protected function SetPriest() {
    if (DB::$ROOM->IsDate(4)) parent::SetPriest();
    return false;
  }

  function Priest(StdClass $role_flag) {
    $event = $this->GetEvent();
    foreach ($role_flag->{$this->role} as $id) {
      $user = DB::$USER->ByID($id);
      $list = $user->GetAround();
      if (DB::$ROOM->IsDummyBoy() && ! in_array(1, $list)) $list[] = 1; //身代わり君を追加
      //Text::p($list, $num);
      $stack = array();
      foreach ($list as $id) {
	$stack[DB::$USER->ByID($id)->GetCamp(true)][] = $id; //陣営カウント
      }
      //Text::p($stack, $uname);
      DB::$ROOM->ResultAbility($event, count(array_keys($stack)), null, $user->id);
    }
  }
}
