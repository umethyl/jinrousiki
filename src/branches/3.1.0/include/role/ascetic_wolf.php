<?php
/*
  ◆印狼 (ascetic_wolf)
  ○仕様
  ・能力結果：九字
  ・投票数：+N (条件つき)
*/
RoleLoader::LoadFile('wolf');
class Role_ascetic_wolf extends Role_wolf {
  public $mix_in = array('authority');

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_ascetic_' . $this->CountAscetic(), null);
  }

  protected function GetVoteDoCount() {
    return floor($this->CountAscetic() / 3);
  }

  //周囲の生存人数取得
  private function CountAscetic() {
    $count = 1;
    foreach (Position::GetAround($this->GetActor()) as $id) {
      if (! DB::$USER->IsVirtualLive($id)) $count++;
    }
    return $count;
  }
}
