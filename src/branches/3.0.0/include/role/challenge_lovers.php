<?php
/*
  ◆難題 (challenge_lovers)
  ○仕様
  ・表示：2 日目以降
  ・ショック死
    + 5 日目以降恋人の相方と同じ人に投票しないとショック死する。
    + 複数の恋人がいる場合は誰か一人と同じならショック死しない。
  ・人狼襲撃耐性：無効 (5 日目以内)
*/
class Role_challenge_lovers extends Role {
  public $mix_in = array('chicken');
  public $sudden_death = 'CHALLENGE';

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  public function IgnoreSuddenDeath() {
    return DB::$ROOM->date < 5;
  }

  public function IsSuddenDeath() {
    if (! is_array($cupid_list = $this->GetStack())){ //QP のデータをセット
      $cupid_list = array();
      foreach (array_keys($this->GetStack('vote_target')) as $uname) {
	$user = DB::$USER->ByRealUname($uname);
	if ($user->IsLovers()) {
	  foreach ($user->GetPartner('lovers') as $id) {
	    $cupid_list[$id][] = $user->id;
	  }
	}
      }
      //Text::p($cupid_list, '◆QP');
      $this->SetStack($cupid_list);
    }
    $target = $this->GetStack('vote_target');
    $stack  = array_keys($target, $target[$this->GetUname()]);
    //Text::p($stack, $this->GetUname());

    $id = $this->GetID();
    foreach ($this->GetActor()->GetPartner('lovers') as $cupid_id) {
      if (! array_key_exists($cupid_id, $cupid_list)) return false;
      foreach ($cupid_list[$cupid_id] as $lovers_id) {
	if ($lovers_id != $id && in_array(DB::$USER->ByID($lovers_id)->uname, $stack)) {
	  return false;
	}
      }
    }
    return true;
  }

  public function WolfEatResist() {
    return $this->GetActor()->IsChallengeLovers();
  }
}
