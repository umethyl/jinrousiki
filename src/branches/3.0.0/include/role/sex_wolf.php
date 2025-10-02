<?php
/*
  ◆雛狼 (sex_wolf)
  ○仕様
  ・襲撃：性別鑑定
*/
RoleManager::LoadFile('wolf');
class Role_sex_wolf extends Role_wolf {
  public $mix_in = array('sex_mage');
  public $result = 'SEX_WOLF_RESULT';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  public function WolfEatAction(User $user) {
    $result = $this->DistinguishSex($user);
    DB::$ROOM->ResultAbility($this->result, $result, $user->GetName(), $this->GetWolfVoter()->id);

    $user->wolf_eat = true; //襲撃は成功扱い
    return true;
  }
}
