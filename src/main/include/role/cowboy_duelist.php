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
  public $mix_in = ['chicken', 'reduce_voter'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillCorrect() {
    //データ取得
    $count_list   = $this->GetStack(VoteDayElement::COUNT_LIST);
    $message_list = $this->GetStack(VoteDayElement::MESSAGE_LIST);

    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($uname == $this->GetVoteKillUname($target_uname)) { //相互投票判定
	continue;
      }

      $actor = DB::$USER->ByUname($uname);
      //織姫は補正をかけない
      if ($actor->IsRole('vega_lovers') && false === DB::$ROOM->IsEvent('no_authority')) {
	continue;
      }

      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsPartner($this->GetPartnerRole(), $actor->id)) { //宿敵判定
	$count_list[$uname] += 5;
	$message_list[$uname]['poll'] += 5;
      }
    }

    //データ保存
    $this->SetStack($count_list,   VoteDayElement::COUNT_LIST);
    $this->SetStack($message_list, VoteDayElement::MESSAGE_LIST);
  }

  public function VoteKillAction() {
    $stack = []; //ショック死対象者リスト
    foreach ($this->GetStackKey() as $uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $user = DB::$USER->ByUname($uname);
      foreach ($this->GetVotePollList($uname) as $target_uname) { //投票者取得
	if ($this->IsVoteKill($target_uname)) {
	  continue;
	}

	$target = DB::$USER->ByRealUname($target_uname);
	if ($target->IsPartner($this->GetPartnerRole(), $user->id)) {
	  $id = ($target_uname == $this->GetVoteKillUname($uname)) ? $target->id : $user->id;
	  $stack[$id] = true;
	}
      }
    }

    foreach ($stack as $id => $flag) { //ショック死処理
      $this->SuddenDeathKill($id);
    }
  }

  protected function GetSuddenDeathType() {
    return 'DUEL';
  }

  protected function FixSelfShoot() {
    return true;
  }
}
