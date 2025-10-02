<?php
/*
  ◆宿敵 (rival)
  ○仕様
  ・仲間表示：憑依追跡なし
  ・勝利判定：生存 + 自分以外の宿敵生存者全滅 (恋人は判定対象外)
*/
class Role_rival extends Role {
  protected function IgnoreImage() {
    return true;
  }

  protected function OutputPartner() {
    $target = $this->GetActor()->partner_list;
    $stack  = array();
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($this->IsRival($user, $target)) {
	$stack[] = $user->handle_name; //憑依追跡なし
      }
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'rival_footer');
  }

  public function FilterWin(&$flag) {
    if (! $flag || $this->GetActor()->IsLovers()) return;
    if ($this->IsDead()) {
      $flag = false;
      return;
    }

    $target = $this->GetActor()->partner_list;
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($this->IsRival($user, $target) && $user->IsLive()) {
	$flag = false;
	return;
      }
    }
  }

  //宿敵判定
  private function IsRival(User $user, array $target) {
    return ! $this->IsActor($user) && $user->IsPartner($this->role, $target);
  }
}
