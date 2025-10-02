<?php
/*
  ◆告白 (confession)
  ○仕様
  ・発言変換：完全置換 (恋人相方に告白)
*/
class Role_confession extends Role {
  const SAY = '%s愛してる！！';

  function ConvertSay() {
    if (! DB::$ROOM->IsDay() || ! Lottery::Percent(3)) return false; //スキップ判定

    $target = $this->GetActor()->partner_list;
    $stack  = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsPartner('lovers', $target)) $stack[] = $user->GetName(); //憑依追跡
    }
    if (count($stack) < 1) return false;

    $this->SetStack(sprintf(self::SAY, Lottery::Get($stack)), 'say');
    return true;
  }
}
