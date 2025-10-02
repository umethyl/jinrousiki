<?php
/*
  ◆諜報員 (agent_escaper)
  ○仕様
  ・逃亡：3の倍数日
  ・逃亡失敗：周囲に人狼系生存
*/
RoleLoader::LoadFile('escaper');
class Role_agent_escaper extends Role_escaper {
  protected function IsAddVote() {
    return DB::$ROOM->date > 2 && DB::$ROOM->date % 3 == 0;
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::IMPOSSIBLE_VOTE_DAY;
  }

  protected function EscapeFailed(User $user) {
    foreach (Position::GetAround($user) as $id) {
      if ($id == $user->id) {
	continue;
      }

      $target = DB::$USER->ByID($id);
      //Text::p($id, "◆Position [{$this->role}]");
      //Text::p("{$target->main_role}/{$target->GetReal()->main_role}", "◆Virtul/Real [{$id}]");
      if (DB::$USER->IsVirtualLive($target->id, true) &&
	  $target->GetReal()->IsMainGroup(CampGroup::WOLF)) {
	return true;
      }
    }
    return false;
  }
}
