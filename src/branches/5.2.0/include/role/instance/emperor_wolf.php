<?php
/*
  ◆帝狼 (emperor_wolf)
  ○仕様
  ・勝利：狂人系全滅
*/
RoleLoader::LoadFile('wolf');
class Role_emperor_wolf extends Role_wolf {
  public function Win($winner) {
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive() && $user->IsMainGroup(CampGroup::MAD)) {
	return false;
      }
    }
    return true;
  }
}
