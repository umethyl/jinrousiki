<?php
/*
  ◆常世神 (missfire_cat)
  ○仕様
  ・蘇生率：30% / 誤爆率：30%
*/
RoleLoader::LoadFile('poison_cat');
class Role_missfire_cat extends Role_poison_cat {
  protected function GetReviveRate() {
    return 30;
  }

  protected function GetMissfireRate($revive) {
    return $this->GetReviveRate();
  }
}
