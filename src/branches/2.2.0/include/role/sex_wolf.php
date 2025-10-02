<?php
/*
  ◆雛狼 (sex_wolf)
  ○仕様
  ・襲撃：性別鑑定
*/
RoleManager::LoadFile('wolf');
class Role_sex_wolf extends Role_wolf {
  public $mix_in = 'sex_mage';
  public $result = 'SEX_WOLF_RESULT';

  protected function OutputResult() {
    if (DB::$ROOM->date > 1) $this->OutputAbilityResult($this->result);
  }

  function WolfEatAction(User $user) {
    $result = $this->DistinguishSex($user);
    DB::$ROOM->ResultAbility($this->result, $result, $user->GetName(), $this->GetWolfVoter()->id);

    $user->wolf_eat = true; //襲撃は成功扱い
    return true;
  }
}
