<?php
/*
  ◆鏡妖精 (mirror_fairy)
  ○仕様
  ・悪戯：決選投票
  ・処刑：特殊イベント (決選投票)
*/
RoleLoader::LoadFile('fairy');
class Role_mirror_fairy extends Role_fairy {
  public $action = VoteAction::CUPID;
  public $submit = VoteAction::FAIRY;

  protected function GetActionDate() {
    return RoleActionDate::FIRST;
  }

  public function VoteKillCounter(array $list) {
    DB::$ROOM->StoreEvent($this->GetID(), EventType::VOTE_DUEL, 1);
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function SetVoteNightTargetList(array $list) {
    $stack = [];
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $this->ValidateVoteNightTarget($user, $user->IsLive());
      $stack[$id] = $user->handle_name;
    }
    $this->SetStack($stack, 'target_list');
  }

  public function SetVoteNightTargetListAction() {
    $stack = $this->GetStack('target_list');
    $this->GetActor()->AddMainRole(ArrayFilter::ConcatKey($stack, '-'));
    $this->SetStack(ArrayFilter::ConcatKey($stack), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($stack), 'target_handle');
    $this->SetStack(VoteAction::FAIRY, 'message'); //Talk の action は FAIRY
  }

  public function SetEvent() {
    $stack = []; //決選投票対象者の ID リスト
    foreach ($this->GetActor()->GetPartner($this->role, true) as $key => $value) { //生存確認
      if (DB::$USER->IsVirtualLive($key)) {
	$stack[] = $key;
      }
      if (DB::$USER->IsVirtualLive($value)) {
	$stack[] = $value;
      }
    }

    if (count($stack) > 1) {
      $event = 'vote_duel';
      DB::$ROOM->Stack()->Set($event, $stack);
      DB::$ROOM->Stack()->Get('event')->On($event);
    }
  }
}
