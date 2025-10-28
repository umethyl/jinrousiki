<?php
/*
  ◆狡狼 (trap_wolf)
  ○仕様
  ・能力結果：発動発現
  ・罠：罠死 (自動自己設置型)
*/
RoleLoader::LoadFile('wolf');
class Role_trap_wolf extends Role_wolf {
  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_trap_wolf', null);
  }

  //罠設置 (自動自己設置型 / 無効判定は呼び出し側で対応)
  final public function SetAutoTrap() {
    $this->AddStack($this->GetID(), RoleVoteTarget::TRAP);
  }
}
