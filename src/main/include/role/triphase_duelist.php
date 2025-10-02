<?php
/*
  ◆三相女神 (triphase_duelist)
  ○仕様
  ・投票人数：3人
  ・追加役職：小心者系・鬼火系 (個別)
*/
RoleLoader::LoadFile('valkyrja_duelist');
class Role_triphase_duelist extends Role_valkyrja_duelist {
  protected function GetVoteNightNeedCount() {
    return 3;
  }

  protected function AddDuelistRole(User $user) {
    foreach ($this->GetAddDuelistRoleList() as $role) { //重複を避けるため個別に付与する
      $user->Addrole($role);
    }
  }

  //追加役職一覧取得
  private function GetAddDuelistRoleList() {
    $stack = $this->GetStack();
    if (is_null($stack)) { //空なら初期設定を行う
      $stack = Lottery::GetList(array(
	array('flattery',  'wisp'),
	array('rabbit', 'black_wisp'),
	array('nervy', 'foughten_wisp')
      ));
    }
    $role_list = array_shift($stack);
    $this->SetStack($stack);
    return $role_list;
  }
}
