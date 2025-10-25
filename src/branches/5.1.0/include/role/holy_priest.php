<?php
/*
  ◆聖徳道士 (holy_priest)
  ○仕様
  ・司祭：身代わり君 + 自分 + 自分周辺の勝利陣営数 (5日目)
*/
RoleLoader::LoadFile('priest');
class Role_holy_priest extends Role_priest {
  protected function IgnoreResult() {
    return false === DateBorder::On(5);
  }

  protected function IgnoreSetPriest() {
    return false === DateBorder::On(4);
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

      $stack = [];
      foreach ($list as $id) {
	$stack[DB::$USER->ByID($id)->GetWinCamp()][] = $id; //陣営カウント
      }
      //Text::p($stack, "◆Camp [{$this->role}]");

      DB::$ROOM->StoreAbility($result, ArrayFilter::CountKey($stack), null, $user->id);
    }
  }
}
