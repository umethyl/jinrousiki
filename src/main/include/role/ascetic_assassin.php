<?php
/*
  ◆修験者 (ascetic_assassin)
  ○仕様
  ・能力結果：九字
  ・人狼襲撃：無効 (確率)
*/
RoleLoader::LoadFile('assassin');
class Role_ascetic_assassin extends Role_assassin {
  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_ascetic_' . $this->CountAscetic(), null);
  }

  public function WolfEatResist() {
    return Lottery::Percent(floor($this->CountAscetic() / 3) * 10);
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
