<?php
/*
  ◆文車妖妃 (letter_cupid)
  ○仕様
  ・恋人抽選：交換日記付与
*/
RoleLoader::LoadFile('cupid');
class Role_letter_cupid extends Role_cupid {
  public function LotteryLovers() {
    $cupid_list = DB::$USER->GetRoleID($this->role);
    //Text::p($cupid_list, "◆{$this->role}");

    $stack = [];
    foreach ($this->GetLoversList() as $id) { //恋人一覧から検索
      $cupid_stack = DB::$USER->ByID($id)->GetPartner('lovers');
      if (count($cupid_stack) > 1) continue; //単独カップルのみ

      $cupid_id = array_shift($cupid_stack);
      if (in_array($cupid_id, $cupid_list)) {
	$stack[$cupid_id][] = $id;
      }
    }
    //Text::p($stack, "◆{$this->role} / Lovers");

    foreach ($stack as $list) {
      if (count($list) != $this->GetVoteNightNeedCount()) continue; //完全単独カップルのみ

      foreach (Lottery::GetList($list) as $key => $id) {
	$user = DB::$USER->ByID($id);
	if ($key == 0) { //先頭を次の送信側とする
	  $user->AddDoom(1, 'letter_exchange');
	  DB::$ROOM->StoreDead($user->handle_name, DeadReason::LETTER_EXCHANGE_MOVED);
	} else {
	  $user->AddDoom(0, 'letter_exchange');
	}
      }
    }
  }
}
