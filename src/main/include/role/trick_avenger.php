<?php
/*
  ◆窮奇 (trick_avenger)
  ○仕様
  ・仇敵：奇術発生
*/
RoleLoader::LoadFile('avenger');
class Role_trick_avenger extends Role_avenger {
  public $mix_in = array('trick_mania');

  public function DuelistAction($target_id) {
    foreach (Text::Parse($target_id) as $id) {
      $user = DB::$USER->ByID($id);
      $this->TrickCopy($user, $this->GetCopyResultRole($user));
    }
  }
}
