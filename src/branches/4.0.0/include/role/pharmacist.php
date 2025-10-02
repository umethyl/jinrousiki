<?php
/*
  ◆薬師 (pharmacist)
  ○仕様
  ・能力結果：毒鑑定/解毒
  ・毒能力鑑定/解毒
*/
class Role_pharmacist extends Role {
  public $result = RoleAbility::PHARMACIST;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  //毒能力情報セット
  final public function SetDetox() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $str = $this->DistinguishPoison(DB::$USER->ByRealUname($target_uname));
      $this->AddStackName($str, 'pharmacist_result', $uname);
    }
  }

  //毒能力鑑定
  final protected function DistinguishPoison(User $user) {
    //非毒能力者・夢毒者 > 強毒者 > 特殊 (騎士・誘毒者・連毒者・毒橋姫) > 通常
    if (! $user->IsRoleGroup('poison') || $user->IsRole('dummy_poison')) {
      return 'nothing';
    } elseif ($user->IsRole('strong_poison')) {
      return 'strong';
    } elseif ($user->IsRole('incubate_poison')) {
      return DB::$ROOM->date > 4 ? 'strong' : 'nothing';
    } elseif ($user->IsRole('poison_guard', 'guide_poison', 'chain_poison', 'poison_jealousy')) {
      return 'limited';
    } else {
      return 'poison';
    }
  }

  //解毒
  public function Detox() {
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
  final public function Cure() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname) || ! $this->IsActor(DB::$USER->ByUname($target_uname))) continue;
      $this->GetActor()->cured_flag = true;
      $this->AddStackName('cured', 'pharmacist_result', $uname);
    }
  }

  //鑑定結果登録
  final public function SaveResult() {
    foreach ($this->GetStack($this->role . '_result') as $uname => $result) {
      $user   = DB::$USER->ByUname($uname);
      $list   = $this->GetStack($user->GetMainRole(true));
      $target = DB::$USER->GetHandleName($list[$user->uname], true);
      DB::$ROOM->ResultAbility($this->result, $result, $target, $user->id);
    }
  }
}
