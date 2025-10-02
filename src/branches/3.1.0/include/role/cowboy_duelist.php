<?php
/*
  ◆無鉄砲者 (cowboy_duelist)
  ○仕様
  ・投票数：-1
  ・得票数補正：+5 (宿敵に投票 & 相互投票ではない)
  ・処刑投票：退治 (宿敵限定 / 投票状況依存)
  ・自分撃ち：固定
*/
RoleLoader::LoadFile('valkyrja_duelist');
class Role_cowboy_duelist extends Role_valkyrja_duelist {
  public $mix_in = array('chicken', 'reduce_voter');

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillCorrect() {
    //データ取得
    $count_list   = $this->GetStack('vote_count');
    $message_list = $this->GetStack('vote_message');

    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($uname == $this->GetVoteTargetUname($target_uname)) continue; //相互投票判定

      $actor = DB::$USER->ByUname($uname);
      if ($actor->IsRole('vega_lovers') && ! DB::$ROOM->IsEvent('no_authority')) {
	continue; //織姫は補正をかけない
      }

      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsPartner($this->GetPartnerRole(), $actor->id)) { //宿敵判定
	$count_list[$uname] += 5;
	$message_list[$uname]['poll'] += 5;
      }
    }

    //データ保存
    $this->SetStack($count_list,   'vote_count');
    $this->SetStack($message_list, 'vote_message');
  }

  public function VoteKillAction() {
    $stack = array(); //ショック死対象者リスト
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;

      $user = DB::$USER->ByUname($uname);
      foreach ($this->GetVotedUname($uname) as $voted_uname) { //投票者取得
	if ($this->IsVoted($voted_uname)) continue;

	$target = DB::$USER->ByRealUname($voted_uname);
	if ($target->IsPartner($this->GetPartnerRole(), $user->id)) {
	  $id = $voted_uname == $this->GetVoteTargetUname($uname) ? $target->id : $user->id;
	  $stack[$id] = true;
	}
      }
    }

    foreach ($stack as $id => $flag) $this->SuddenDeathKill($id); //ショック死処理
  }

  protected function GetSuddenDeathType() {
    return 'DUEL';
  }

  protected function FixSelfShoot() {
    return true;
  }
}
