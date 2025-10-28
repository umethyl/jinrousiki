<?php
/*
  ◆蛻庵 (monk_fox)
  ○仕様
  ・能力結果：霊能 (70%)
  ・霊能：通常
*/
RoleLoader::LoadFile('child_fox');
class Role_monk_fox extends Role_child_fox {
  public $mix_in = ['necromancer'];
  public $action = null;
  public $result = RoleAbility::MONK_FOX;

  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  public function Necromancer(User $user, $flag) {
    if (true === $flag || Lottery::Percent(30)) {
      return 'stolen';
    } else {
      return $this->DistinguishNecromancer($user);
    }
  }
}
