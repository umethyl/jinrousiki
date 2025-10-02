<?php
/*
  ◆鏡妖精 (mirror_fairy)
  ○仕様
  ・悪戯：決選投票
  ・処刑：特殊イベント (決選投票)
*/
RoleManager::LoadFile('fairy');
class Role_mirror_fairy extends Role_fairy {
  public $action = 'CUPID_DO';
  public $action_date_type = 'first';
  public $submit = 'fairy_do';
  public $event_day = 'vote_duel';

  public function IsVoteCheckbox(User $user, $live) {
    return $live && ! $user->IsDummyBoy();
  }

  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('checkbox');
  }

  public function SetVoteNightUserList(array $list) {
    $stack = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      //例外判定
      if ($user->IsDead())     return VoteRoleMessage::TARGET_DEAD;
      if ($user->IsDummyBoy()) return VoteRoleMessage::TARGET_DUMMY_BOY;
      $stack[$id] = $user->handle_name;
    }
    $this->SetStack($stack, 'target_list');
    return null;
  }

  public function VoteNightAction() {
    $stack = $this->GetStack('target_list');
    $this->GetActor()->AddMainRole(implode('-', array_keys($stack)));
    $this->SetStack(implode(' ', array_keys($stack)), 'target_no');
    $this->SetStack(implode(' ', $stack), 'target_handle');
    $this->SetStack('FAIRY_DO', 'message'); //Talk の action は FAIRY_DO
  }

  public function VoteKillCounter(array $list) {
    DB::$ROOM->SystemMessage($this->GetID(), 'VOTE_DUEL', 1);
  }

  public function SetEvent(UserData $USERS) {
    $stack = array(); //決選投票対象者の ID リスト
    foreach ($this->GetActor()->GetPartner($this->role, true) as $key => $value) { //生存確認
      if ($USERS->IsVirtualLive($key))   $stack[] = $key;
      if ($USERS->IsVirtualLive($value)) $stack[] = $value;
    }

    if (count($stack) > 1) {
      DB::$ROOM->Stack()->Set($this->event_day, $stack);
      DB::$ROOM->Stack()->Get('event')->On($this->event_day);
    }
  }
}
