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
  public $mix_in = ['chicken'];

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  protected function IgnoreSuddenDeath() {
    return DB::$ROOM->date < 5;
  }

  protected function IsSuddenDeath() {
    $role = 'lovers';
    $cupid_list = $this->GetStack(); //QP データ
    if (! is_array($cupid_list)) { //未設定なら登録
      $cupid_list = [];
      foreach ($this->GetStackKey(VoteDayElement::TARGET_LIST) as $uname) {
	$user = DB::$USER->ByRealUname($uname);
	if (! $user->IsRole($role)) continue;

	foreach ($user->GetPartner($role) as $id) {
	  $cupid_list[$id][] = $user->id;
	}
      }
      //Text::p($cupid_list, '◆QP');
      $this->SetStack($cupid_list);
    }
    $target = $this->GetStack(VoteDayElement::TARGET_LIST);
    $stack  = array_keys($target, $target[$this->GetUname()]);
    //Text::p($stack, "◆VoteTarget/{$this->GetUname()} [{$this->role}]");

    $id = $this->GetID();
    foreach ($this->GetActor()->GetPartner($role) as $cupid_id) {
      //難題持ちで自分のキューピッドが見つからない場合は抜けておく
      if (! isset($cupid_list[$cupid_id])) return false;

      foreach ($cupid_list[$cupid_id] as $lovers_id) {
	if ($lovers_id != $id && in_array(DB::$USER->ByID($lovers_id)->uname, $stack)) {
	  return false;
	}
      }
    }
    return true;
  }

  protected function GetSuddenDeathType() {
    return 'CHALLENGE';
  }

  public function WolfEatResist() {
    return RoleUser::IsChallengeLovers($this->GetActor());
  }
}
