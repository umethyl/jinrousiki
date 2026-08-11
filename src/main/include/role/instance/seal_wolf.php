<?php
/*
  ◆封狼 (seal_wolf)
  ○仕様
  ・占い：呪殺
  ・襲撃カウンター無効化：あり
*/
RoleLoader::LoadFile('wolf');
class Role_seal_wolf extends Role_wolf {
  public function DisableWolfEatCount() {
    return true;
  }
}
