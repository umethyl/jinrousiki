<?php
/*
  ◆吸血鬼 (vampire)
  ○仕様
  ・吸血：通常
  ・仲間表示：感染者・洗脳者
*/
class Role_vampire extends Role {
  public $action = VoteAction::VAMPIRE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IgnorePartner() {
    /* 1日目の時点で感染者・洗脳者が発生する特殊イベントを実装したら対応すること */
    return DB::$ROOM->date < 2;
  }

  protected function GetPartner() {
    $id        = $this->GetID();
    $main      = 'infected';
    $sub       = 'psycho_infected';
    $main_list = [];
    $sub_list  = [];
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsPartner($main, $id)) {
	$main_list[] = $user->handle_name;
      }
      if ($user->IsRole($sub)) {
	$sub_list[]  = $user->handle_name;
      }
    }
    return [$main . '_list' => $main_list,  $sub . '_list' => $sub_list];
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::VAMPIRE, RoleAbilityMessage::VAMPIRE, $this->action);
  }

  //吸血対象セット
  final public function SetInfect(User $user) {
    $actor = $this->GetActor();
    $this->SetStack($actor, 'voter');
    if (RoleUser::DelayTrap($actor, $user->id)) { //罠判定
      return;
    }

    //追加吸血判定
    foreach ($this->GetStackKey(RoleVoteTarget::ESCAPER, $actor->id) as $id) { //自己逃亡判定
      $this->SetInfectTarget($id);
    }
    foreach ($this->GetStackKey(RoleVoteTarget::ESCAPER, $user->id)  as $id) { //逃亡巻き添え判定
      $this->SetInfectTarget($id);
    }

    //無効判定 (護衛 > 死亡 > 逃亡)
    if (RoleUser::Guard($user) || $user->IsDead(true) || RoleUser::IsEscape($user)) {
      return;
    }

    //吸血リスト登録 (吸血鬼襲撃 > 時間差コピー能力者 > 通常)
    if ($user->IsMainGroup(CampGroup::VAMPIRE)) {
      RoleLoader::LoadMain($user)->InfectVampire($actor);
    } elseif (RoleUser::IsDelayCopy($user) && $user->IsCamp(Camp::VAMPIRE)) {
      $this->DelayInfectKill($user);
    } else {
      $this->SetInfectTarget($user->id);
    }
  }

  //吸血リスト登録
  final protected function SetInfectTarget($id) {
    $stack = $this->GetStack('vampire');
    $stack[$this->GetVoter()->id][] = $id;
    $this->SetStack($stack, 'vampire');
  }

  //対吸血処理
  protected function InfectVampire(User $user) {
    $this->DelayInfectKill($this->GetActor());
  }

  //吸血死対象登録
  final protected function DelayInfectKill(User $user) {
    if (false === RoleUser::IsAvoid($user)) {
      $this->AddSuccess($user->id, RoleVoteSuccess::VAMPIRE_KILL);
    }
  }

  //吸血死＆吸血処理
  final public function VampireKill() {
    foreach ($this->GetStack(RoleVoteSuccess::VAMPIRE_KILL) as $id => $flag) { //吸血死処理
      DB::$USER->Kill($id, DeadReason::VAMPIRE_KILLED);
    }

    foreach ($this->GetStack('vampire') as $id => $stack) {
      $filter = RoleLoader::LoadMain(DB::$USER->ByID($id));
      foreach ($stack as $target_id) {
	$filter->Infect(DB::$USER->ByID($target_id));
      }
    }
  }

  //吸血処理
  final protected function Infect(User $user) {
    if ($this->IsInfect($user)) {
      $user->AddRole($this->GetActor()->GetID('infected'));
    } else {
      $this->InfectFailedAction($user);
    }
    $this->InfectAction($user);
  }

  //吸血実行判定
  protected function IsInfect(User $user) {
    return true;
  }

  //吸血失敗追加処理
  protected function InfectFailedAction(User $user) {}

  //吸血追加処理
  protected function InfectAction(User $user) {}

  //勝敗判定
  final public function CheckWin() {
    /* 情報収集 */
    $live_list     = []; //生存者の ID リスト
    $infected_list = []; //吸血鬼 => 感染者リスト
    foreach (DB::$USER->SearchLive(true) as $id => $uname) {
      $user = DB::$USER->ByID($id);
      $user->Reparse();
      if (false === $user->IsRole('psycho_infected')) {
	$live_list[] = $user->id;
      }

      if ($user->IsRole('infected')) {
	foreach ($user->GetPartner('infected') as $id) {
	  $infected_list[$id][] = $user->id;
	}
      }
    }
    //Text::p($live_list,     "◆{$this->role} [live]");
    //Text::p($infected_list, "◆{$this->role} [infected]");

    /* 判定 */
    if (count($live_list) == 1) { //単独生存
      return DB::$USER->ByID(array_shift($live_list))->IsMainGroup(CampGroup::VAMPIRE);
    }

    foreach ($infected_list as $id => $stack) { //支配判定
      $diff_list = array_diff($live_list, $stack);
      //Text::p($diff_list, "◆{$this->role} [diff/{$id}]");
      if (count($diff_list) == 1 && in_array($id, $diff_list)) {
	return true;
      }
    }
    return false;
  }
}
