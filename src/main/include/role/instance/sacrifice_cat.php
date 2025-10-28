<?php
/*
  ◆猫神 (sacrifice_cat)
  ○仕様
  ・蘇生率：100% / 誤爆無し
  ・蘇生後：死亡
*/
RoleLoader::LoadFile('poison_cat');
class Role_sacrifice_cat extends Role_poison_cat {
  protected function GetReviveRate() {
    return 100;
  }

  protected function GetMissfireRate($revive) {
    return 0;
  }

  protected function ReviveAction() {
    DB::$USER->Kill($this->GetID(), DeadReason::SACRIFICE);
  }
}
