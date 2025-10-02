<?php
/*
  ◆殉教者 (immolate_mad)
  ○仕様
  ・人狼襲撃得票カウンター：能力発現
  ・勝利：能力発現所持
*/
class Role_immolate_mad extends Role {
  public $ability = 'muster_ability';

  function WolfEatReaction() {
    $this->GetActor()->AddRole($this->ability);
    return false;
  }

  function Win($winner) { return $this->GetActor()->IsRole($this->ability); }
}
