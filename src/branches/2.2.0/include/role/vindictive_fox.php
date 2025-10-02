<?php
/*
  ◆昼狐 (vindictive_fox)
  ○仕様
  ・変化：妖狐
*/
RoleManager::LoadFile('child_fox');
class Role_vindictive_fox extends Role_child_fox {
  public $mix_in = null;
  public $action = null;
  public $result = null;

  //変化処理 (トリガーは呼び出し側制御)
  final function Change() {
    $user = $this->GetActor();
    $user->ReplaceRole($user->main_role, 'fox');
    $user->AddRole('changed_vindictive');
  }
}
