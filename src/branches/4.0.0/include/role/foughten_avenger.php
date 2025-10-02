<?php
/*
  ◆落ち武者 (foughten_avenger)
  ○仕様
  ・追加役職：古戦場火
*/
RoleLoader::LoadFile('avenger');
class Role_foughten_avenger extends Role_avenger {
  protected function AddDuelistRole(User $user) {
    $stack = $this->GetStack();
    if (! is_array($stack)) { //抽選処理
      $list  = Lottery::GetList($this->GetStackKey('target_list'));
      $stack = array_slice($list, 0, floor(count($list) / 2));
      $this->SetStack($stack);
    }
    //Text::p($stack, "◆Target [{$this->role}]");
    if ($this->InStack($user->id)) {
      $user->AddRole('foughten_wisp');
    }
  }
}
