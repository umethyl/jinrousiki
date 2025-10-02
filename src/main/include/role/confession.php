<?php
/*
  ◆告白 (confession)
  ○仕様
  ・役職表示：無し
  ・発言変換：完全置換 (恋人相方に告白)
*/
class Role_confession extends Role {
  const SAY = '%s愛してる！！';

  protected function IgnoreImage() {
    return true;
  }

  public function ConvertSay() {
    if (! DB::$ROOM->IsDay() || ! Lottery::Percent(3)) return false; //スキップ判定

    $role   = 'lovers';
    $target = $this->GetActor()->GetPartnerList();
    $stack  = array();
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsPartner($role, $target)) {
	$stack[] = $user->GetName(); //憑依追跡
      }
    }
    if (count($stack) < 1) return false;

    $this->SetStack(sprintf(self::SAY, Lottery::Get($stack)), 'say');
    return true;
  }
}
