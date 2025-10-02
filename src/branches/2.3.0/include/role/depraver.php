<?php
/*
  ◆背徳者 (depraver)
  ○仕様
  ・仲間表示：妖狐系・子狐系
  ・勝利：妖狐陣営勝利 or 生存 (妖狐陣営不在時)
*/
class Role_depraver extends Role {
  protected function OutputPartner() {
    $stack = array(); //妖狐
    foreach (DB::$USER->rows as $user) {
      if ($user->IsRole('possessed_fox')) {
	$stack[] = $user->GetName(); //憑依追跡
      }
      elseif ($user->IsFoxCount()) {
	if (! $user->IsLonely()) $stack[] = $user->handle_name;
      }
      elseif ($user->IsRoleGroup('scarlet')) {
	$stack[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($stack, 'depraver_partner');
  }

  protected function OutputAddResult() {
    if (DB::$USER->GetFoxCount() < 1) Image::Role()->Output('depraver_no_fox');
  }
}
