<?php
/*
  ◆殉教者 (immolate_mad)
  ○仕様
  ・人狼襲撃得票：能力発現
  ・勝利：能力発現所持
*/
class Role_immolate_mad extends Role {
  public function WolfEatReaction() {
    $this->AddMusterRole();
    return false;
  }

  //能力発現処理
  final protected function AddMusterRole() {
    $this->GetActor()->AddRole($this->GetMusterRole());
  }

  //発現役職取得
  final protected function GetMusterRole() {
    return 'muster_ability';
  }

  public function Win($winner) {
    return $this->IsMusterRole();
  }

  //能力発現判定
  final protected function IsMusterRole() {
    return $this->GetActor()->IsRole($this->GetMusterRole());
  }
}
