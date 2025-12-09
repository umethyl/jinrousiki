<?php
/*
  ◆境界師 (border_priest)
  ○仕様
  ・司祭：自分への投票人数 (2日目以降)
*/
RoleLoader::LoadFile('priest');
class Role_border_priest extends Role_priest {
  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  protected function IgnoreSetPriest() {
    return DateBorder::PreTwo();
  }

  protected function IsAggregatePriestCamp() {
    return false;
  }

  protected function PriestAction() {
    $result = $this->GetPriestResultType();
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      //スキップ判定 (司祭能力発動は蘇生判定の後)
      if ($user->IsDead(true) || $user->IsOn(UserMode::REVIVE)) {
	continue;
      }

      $count = 0;
      foreach (DB::$ROOM->Stack()->Get('vote') as $vote_stack) {
	foreach ($vote_stack as $action => $stack) {
	  //複合投票タイプに備えておく (生存限定 + 自己投票を検出するので憑依は追跡しない)
	  foreach (Text::Parse($stack['target_no']) as $id) {
	    if ($id == $user->id) {
	      $count++;
	    }
	  }
	}
      }
      DB::$ROOM->StoreAbility($result, $count, null, $user->id);
    }
  }
}
