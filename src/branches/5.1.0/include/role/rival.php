<?php
/*
  ◆宿敵 (rival)
  ○仕様
  ・役職表示：無し
  ・仲間表示：対象者 (憑依追跡なし)
  ・勝利判定：生存 + 自分以外の宿敵生存者全滅 (恋人は判定対象外)
*/
class Role_rival extends Role {
  protected function IgnoreImage() {
    return true;
  }

  protected function GetPartner() {
    $target = $this->GetActor()->GetPartnerList();
    $stack  = [];
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($this->IsRival($user, $target)) {
	$stack[] = $user->handle_name; //憑依追跡なし
      }
    }
    return ['partner_header' => $stack];
  }

  protected function OutputPartnerByType(array $list, $type) {
    RoleHTML::OutputPartner($list, $type, 'rival_footer');
  }

  public function FilterWin(&$flag) {
    if (! $flag || $this->GetActor()->IsRole('lovers')) {
      return;
    }

    if ($this->IsActorDead()) {
      $flag = false;
      return;
    }

    $target = $this->GetActor()->GetPartnerList();
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($this->IsRival($user, $target) && $user->IsLive()) {
	$flag = false;
	return;
      }
    }
  }

  //宿敵判定
  private function IsRival(User $user, array $target) {
    return false === $this->IsActor($user) && $user->IsPartner($this->role, $target);
  }
}
