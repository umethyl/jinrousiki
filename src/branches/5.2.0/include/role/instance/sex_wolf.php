<?php
/*
  ◆雛狼 (sex_wolf)
  ○仕様
  ・能力結果：襲撃
  ・襲撃：性別鑑定
  ・妖狐襲撃得票カウンター：無効
*/
RoleLoader::LoadFile('wolf');
class Role_sex_wolf extends Role_wolf {
  public $mix_in = ['sex_mage'];
  public $result = RoleAbility::SEX_WOLF;

  protected function IgnoreResult() {
    return DateBorder::PreTwo();
  }

  public function WolfEatAction(User $user) {
    $result = $this->DistinguishSex($user);
    DB::$ROOM->StoreAbility($this->result, $result, $user->GetName(), $this->GetWolfVoter()->id);

    $user->wolf_eat = true; //襲撃は成功扱い
    return true;
  }

  public function EnableWolfEatFoxReaction() {
    return false;
  }
}
