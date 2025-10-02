<?php
/*
  ◆蝕仙狸 (eclipse_cat)
  ○仕様
  ・役職表示：仙狸
  ・蘇生率：40% / 誤爆率：20%
*/
RoleLoader::LoadFile('poison_cat');
class Role_eclipse_cat extends Role_poison_cat {
  public $display_role  = 'revive_cat';

  protected function GetReviveRate() {
    return 40;
  }

  protected function GetMissfireRate($revive) {
    return 20;
  }
}
