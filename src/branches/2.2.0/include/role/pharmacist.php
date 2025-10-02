<?php
/*
  ◆薬師 (pharmacist)
  ○仕様
  ・毒能力鑑定/解毒
*/
class Role_pharmacist extends Role {
  public $result = 'PHARMACIST_RESULT';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2) $this->OutputAbilityResult($this->result);
  }

  function SetVoteDay($uname) {
    $this->InitStack();
    if ($this->IsRealActor()) $this->AddStackName($uname);
  }

  //毒能力情報セット
  function SetDetox() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $str = $this->DistinguishPoison(DB::$USER->ByRealUname($target_uname));
      $this->AddStackName($str, 'pharmacist_result', $uname);
    }
  }

  //毒能力鑑定
  protected function DistinguishPoison(User $user) {
    //非毒能力者・夢毒者
    if (! $user->IsRoleGroup('poison') || $user->IsRole('dummy_poison')) return 'nothing';
    if ($user->IsRole('strong_poison')) return 'strong'; //強毒者
    if ($user->IsRole('incubate_poison')) {
      return DB::$ROOM->date > 4 ? 'strong' : 'nothing'; //潜毒者
    }
    if ($user->IsRole('poison_guard', 'guide_poison', 'chain_poison', 'poison_jealousy')) {
      return 'limited'; //騎士・誘毒者・連毒者・毒橋姫
    }
    return 'poison';
  }

  //解毒
  function Detox() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      if ($this->IsActor(DB::$USER->ByUname($target_uname))) $this->SetDetoxFlag($uname);
    }
  }

  //解毒フラグセット
  protected function SetDetoxFlag($uname) {
    $this->GetActor()->detox = true;
    $this->AddStackName('success', 'pharmacist_result', $uname);
  }

  //ショック死抑制
  function Cure() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname) || ! $this->IsActor(DB::$USER->ByUname($target_uname))) continue;
      $this->GetActor()->cured_flag = true;
      $this->AddStackName('cured', 'pharmacist_result', $uname);
    }
  }

  //鑑定結果登録
  function SaveResult() {
    foreach ($this->GetStack($this->role . '_result') as $uname => $result) {
      $user   = DB::$USER->ByUname($uname);
      $list   = $this->GetStack($user->GetMainRole(true));
      $target = DB::$USER->GetHandleName($list[$user->uname], true);
      DB::$ROOM->ResultAbility($this->result, $result, $target, $user->id);
    }
  }
}
