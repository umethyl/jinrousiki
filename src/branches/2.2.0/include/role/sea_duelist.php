<?php
/*
  ◆海御前 (sea_duelist)
  ○仕様
  ・処刑投票：退治 (宿敵限定)
*/
RoleManager::LoadFile('valkyrja_duelist');
class Role_sea_duelist extends Role_valkyrja_duelist {
  public $mix_in = 'critical_mad';
  public $self_shoot = true;
  public $sudden_death = 'DUEL';

  function VoteAction() {
    $stack = array(); //ショック死対象者リスト
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname) || $this->IsVoted($target_uname)) continue;

      $user   = DB::$USER->ByUname($uname);
      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsPartner($this->partner_role, $user->id)) $stack[$user->id] = true;
    }

    foreach ($stack as $id => $flag) $this->SuddenDeathKill($id); //ショック死処理
  }
}
