<?php
/*
  ◆惑溺 (infatuated)
  ○仕様
  ・処刑投票情報収集：道連れ (仮想的に自分で自分に投票する舟幽霊を設定する)
*/
class Role_infatuated extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ETC;
  }

  protected function SetStackVoteKillEtc($name) {
    if (DB::$ROOM->IsEvent('no_sudden_death')) return; //凪ならスキップ

    $role = 'follow_mad';
    RoleLoader::Load($role);
    $this->AddStackName($this->GetUname(), $role);
    //Text::p($this->GetStack($role), "◆[{$this->role}]");
  }
}
