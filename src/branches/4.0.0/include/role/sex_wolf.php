<?php
/*
  ◆雛狼 (sex_wolf)
  ○仕様
  ・能力結果：襲撃
  ・襲撃：性別鑑定
*/
RoleLoader::LoadFile('wolf');
class Role_sex_wolf extends Role_wolf {
  public $mix_in = ['sex_mage'];
  public $result = RoleAbility::SEX_WOLF;

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
