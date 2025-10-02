<?php
/*
  ◆寿羊狐 (critical_fox)
  ○仕様
  ・仲間表示：子狐枠
  ・処刑投票：痛恨 (妖狐系)
  ・勝利：妖狐系全滅
*/
RoleLoader::LoadFile('child_fox');
class Role_critical_fox extends Role_child_fox {
  public $mix_in = array('critical_mad');
  public $action = null;
  public $result = null;

  protected function FilterPartner(array $list) {
    unset($list['fox_partner']);
    return $list;
  }

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function IsVoteKillActionTarget(User $user) {
    return $this->IsFoxGroup($user);
  }

  //妖狐系判定
  private function IsFoxGroup(User $user) {
    return $user->IsMainGroup(CampGroup::FOX);
  }

  public function Win($winner) {
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive() && $this->IsFoxGroup($user)) {
	return false;
      }
    }
    return true;
  }
}
