<?php
/*
  ◆無鉄砲者 (cowboy_duelist)
  ○仕様
  ・投票数：-1
  ・処刑投票補正：宿敵に投票 + 相互投票ではない場合に得票数 +5
  ・処刑投票：退治 (宿敵限定 / 投票状況依存)
*/
RoleManager::LoadFile('valkyrja_duelist');
class Role_cowboy_duelist extends Role_valkyrja_duelist {
  public $mix_in = 'critical_mad';
  public $self_shoot = true;
  public $sudden_death = 'DUEL';

  function FilterVoteDo(&$count) { $count--; }

  function VoteCorrect(array &$message_list, array &$count_list) {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($uname == $this->GetVoteTargetUname($target_uname)) continue; //相互投票判定

      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsPartner($this->partner_role, DB::$USER->ByUname($uname)->id)) { //宿敵判定
	$message_list[$uname]['poll'] += 5;
	$count_list[$uname] += 5;
      }
    }
  }

  function VoteAction() {
    $stack = array(); //ショック死対象者リスト
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;

      $user = DB::$USER->ByUname($uname);
      foreach ($this->GetVotedUname($uname) as $voted_uname) { //投票者取得
	if ($this->IsVoted($voted_uname)) continue;

	$target = DB::$USER->ByRealUname($voted_uname);
	if ($target->IsPartner($this->partner_role, $user->id)) {
	  $id = $voted_uname == $this->GetVoteTargetUname($uname) ? $target->id : $user->id;
	  $stack[$id] = true;
	}
      }
    }

    foreach ($stack as $id => $flag) $this->SuddenDeathKill($id); //ショック死処理
  }
}
