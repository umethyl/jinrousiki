<?php
/*
  ◆背徳者 (depraver)
  ○仕様
  ・勝利：妖狐陣営勝利 or 生存 (妖狐陣営不在時)
  ・仲間表示：妖狐・子狐枠
  ・能力結果：妖狐不在通知
*/
class Role_depraver extends Role {
  protected function GetPartner() {
    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsRole('possessed_fox')) {
	$stack[] = $user->GetName(); //憑依追跡
      } elseif (RoleUser::IsFoxCount($user)) {
	if (! RoleUser::IsLonely($user)) {
	  $stack[] = $user->handle_name;
	}
      } elseif ($user->IsRoleGroup('scarlet')) {
	$stack[] = $user->handle_name;
      }
    }
    return ['depraver_partner' => $stack];
  }

  protected function OutputAddResult() {
    if (DB::$USER->GetFoxCount() < 1) {
      ImageManager::Role()->Output('depraver_no_fox');
    }
  }
}
