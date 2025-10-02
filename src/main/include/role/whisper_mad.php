<?php
/*
  ◆囁き狂人 (whisper_mad)
  ○仕様
*/
class Role_whisper_mad extends Role {
  protected function OutputPartner() {
    $wolf = array();
    $mad  = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsRole('possessed_wolf')) {
	$wolf[] = $user->GetName(); //憑依先を追跡する
      }
      elseif ($user->IsWolf(true)) {
	$wolf[] = $user->handle_name;
      }
      elseif ($user->IsRole($this->role)) {
	$mad[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($wolf, 'wolf_partner');
    RoleHTML::OutputPartner($mad, 'mad_partner');
  }
}
