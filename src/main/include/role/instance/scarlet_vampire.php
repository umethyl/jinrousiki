<?php
/*
  ◆屍鬼 (scarlet_vampire)
  ○仕様
  ・人狼襲撃：確率蘇生
*/
RoleLoader::LoadFile('vampire');
class Role_scarlet_vampire extends Role_vampire {
  public $mix_in = ['revive_pharmacist'];

  protected function IsResurrect() {
    return Lottery::Percent(DB::$ROOM->IsEvent('full_revive') ? 100 : 40);
  }

  protected function IsResurrectLost() {
    return false;
  }
}
