<?php
/*
  ◆鋭狼 (sharp_wolf)
  ○仕様
  ・能力結果：襲撃 (天啓封印あり)
  ・襲撃：危機回避
*/
RoleLoader::LoadFile('wolf');
class Role_sharp_wolf extends Role_wolf {
  public $result = RoleAbility::SHARP_WOLF;

  protected function IgnoreResult() {
    return DateBorder::PreTwo();
  }

  public function WolfEatAction(User $user) {
    if (false === $user->IsMainGroup(CampGroup::MAD) && false === RoleUser::IsPoison($user)) {
      return false;
    }

    $id = $this->GetWolfVoter()->id;
    DB::$ROOM->StoreAbility($this->result, 'wolf_avoid', $user->GetName(), $id);
    return true;
  }
}
