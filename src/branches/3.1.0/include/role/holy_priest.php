<?php
/*
  ◆聖徳道士 (holy_priest)
  ○仕様
  ・司祭：身代わり君 + 自分 + 自分周辺の勝利陣営数 (5日目)
*/
RoleLoader::LoadFile('priest');
class Role_holy_priest extends Role_priest {
  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(5);
  }

  protected function IgnoreSetPriest() {
    return ! DB::$ROOM->IsDate(4);
  }

  protected function IsAggregatePriestCamp() {
    return false;
  }

  protected function PriestAction() {
    $result = $this->GetPriestResultType();
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      $list = Position::GetAround($user);
      if (DB::$ROOM->IsDummyBoy()) {
	ArrayFilter::Register($list, DB::$USER->GetDummyBoyID()); //身代わり君を追加
      }
      //Text::p($list, "◆Around [{$this->role}]");

      $stack = array();
      foreach ($list as $id) {
	$stack[DB::$USER->ByID($id)->GetWinCamp()][] = $id; //陣営カウント
      }
      //Text::p($stack, "◆Camp [{$this->role}]");

      DB::$ROOM->ResultAbility($result, count(array_keys($stack)), null, $user->id);
    }
  }
}
