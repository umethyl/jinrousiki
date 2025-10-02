<?php
/*
  ◆天狼 (sirius_wolf)
  ○仕様
*/
RoleManager::LoadFile('wolf');
class Role_sirius_wolf extends Role_wolf {
  protected function OutputResult() {
    switch (strval(count(DB::$USER->GetLivingWolves()))) { //覚醒状態
    case '2':
      RoleHTML::OutputAbilityResult('ability_sirius_wolf', null);
      break;

    case '1':
      RoleHTML::OutputAbilityResult('ability_full_sirius_wolf', null);
      break;
    }
  }
}
