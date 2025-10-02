<?php
/*
  ◆橋姫 (jealousy)
  ○仕様
  ・処刑得票：ショック死 (同一キューピッド恋人限定)
*/
class Role_jealousy extends Role {
  public $vote_day_type = 'init';
  public $sudden_death  = 'JEALOUSY';

  public function VotedReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      if ($this->IsVoted($uname)) continue;

      $cupid_list = array(); //橋姫に投票したユーザのキューピッドの ID => 恋人の ID
      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	$user = DB::$USER->ByRealUname($voted_uname);
	foreach ($user->GetPartner('lovers', true) as $id) {
	  $cupid_list[$id][] = $user->id;
	}
      }

      //同一キューピッドの恋人が複数いたらショック死
      foreach ($cupid_list as $cupid_id => $lovers_list) {
	if (count($lovers_list) > 1) {
	  foreach ($lovers_list as $id) $this->SuddenDeathKill($id);
	}
      }
    }
  }
}
