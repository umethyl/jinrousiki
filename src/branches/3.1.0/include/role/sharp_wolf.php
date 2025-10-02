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
    return DB::$ROOM->date < 2 || DB::$ROOM->IsOption('seal_message');
  }

  public function WolfEatAction(User $user) {
    if (! $user->IsMainGroup(CampGroup::MAD) && ! RoleUser::IsPoison($user)) return false;
    if (DB::$ROOM->IsOption('seal_message')) return true;
    $id = $this->GetWolfVoter()->id;
    DB::$ROOM->ResultAbility($this->result, 'wolf_avoid', $user->GetName(), $id);
    return true;
  }
}
