<?php
/*
  ◆蛻庵 (monk_fox)
  ○仕様
  ・能力結果：霊能
  ・霊能：通常
*/
RoleLoader::LoadFile('child_fox');
class Role_monk_fox extends Role_child_fox {
  public $mix_in = array('necromancer');
  public $action = null;
  public $result = RoleAbility::MONK_FOX;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  public function Necromancer(User $user, $flag) {
    return ($flag || Lottery::Percent(30)) ? 'stolen' : $this->DistinguishNecromancer($user);
  }
}
