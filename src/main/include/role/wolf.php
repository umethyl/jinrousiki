<?php
/*
  ◆人狼 (wolf)
  ○仕様
*/
class Role_wolf extends Role {
  public $action = 'WOLF_EAT';
  public $wolf_action_list = array('WOLF_EAT', 'STEP_WOLF_EAT', 'SILENT_WOLF_EAT');

  protected function OutputPartner() {
    $wolf_list        = array();
    $mad_list         = array();
    $unconscious_list = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsRole('possessed_wolf')) {
	$wolf_list[] = $user->GetName(); //憑依先を追跡する
      }
      elseif ($user->IsWolf(true)) {
	$wolf_list[] = $user->handle_name;
      }
      elseif ($user->IsRole('whisper_mad')) {
	$mad_list[] = $user->handle_name;
      }
      elseif ($user->IsRole('unconscious') || $user->IsRoleGroup('scarlet')) {
	$unconscious_list[] = $user->handle_name;
      }
    }
    if ($this->GetActor()->IsWolf(true)) {
      RoleHTML::OutputPartner($wolf_list, 'wolf_partner'); //人狼
      RoleHTML::OutputPartner($mad_list, 'mad_partner'); //囁き狂人
    }
    if (DB::$ROOM->IsNight()) {
      RoleHTML::OutputPartner($unconscious_list, 'unconscious_list'); //無意識
    }
  }

  function OutputAction() {
    RoleHTML::OutputVote('wolf-eat', 'wolf_eat', $this->action);
  }

  //身代わり君襲撃固定判定
  final function IsDummyBoy() {
    return DB::$ROOM->IsQuiz() || (DB::$ROOM->IsDummyBoy() && DB::$ROOM->IsDate(1));
  }

  function IsVoteCheckboxChecked(User $user) { return $this->IsDummyBoy() && $user->IsDummyBoy(); }

  function ExistsAction(array $list) {
    if (DB::$ROOM->IsEvent('no_step')) unset($list['SILENT_WOLF_EAT']);
    return count(array_intersect($this->wolf_action_list, array_keys($list))) > 0;
  }

  //遠吠え
  function Howl(TalkBuilder $builder, $voice) {
    if (! $builder->flag->wolf_howl) return false; //スキップ判定

    $str = Message::$wolf_howl;
    foreach ($builder->filter as $filter) $filter->FilterWhisper($voice, $str); //フィルタリング処理
    $builder->AddRaw('', '狼の遠吠え', $str, $voice);
    return true;
  }

  function GetVoteTargetUser() {
    $stack = parent::GetVoteTargetUser();
    //身代わり君適用判定
    if ($this->IsDummyBoy()) $stack = array(1 => $stack[1]); //dummy_boy = 1番は保証されている？
    return $stack;
  }

  function GetVoteIconPath(User $user, $live) {
    return ! $live ? Icon::GetDead() :
      ($this->IsWolfPartner($user->id) ? Icon::GetWolf() : Icon::GetFile($user->icon_filename));
  }

  //仲間狼判定
  protected function IsWolfPartner($id) { return DB::$USER->ByReal($id)->IsWolf(true); }

  function IsVoteCheckbox(User $user, $live) {
    return parent::IsVoteCheckbox($user, $live) && $this->IsWolfEatTarget($user->id);
  }

  //仲間狼襲撃可能判定
  protected function IsWolfEatTarget($id) { return ! $this->IsWolfPartner($id); }

  function IgnoreVoteNight(User $user, $live) {
    if (! is_null($str = parent::IgnoreVoteNight($user, $live))) return $str;
    if (! $user->IsDummyBoy()) { //身代わり君判定
      if (DB::$ROOM->IsQuiz()) return 'クイズ村では GM 以外に投票できません'; //クイズ村
      if (DB::$ROOM->IsDummyBoy() && DB::$ROOM->IsDate(1)) { //身代わり君
	return '身代わり君使用の場合は、身代わり君以外に投票できません';
      }
    }
    if (! $this->IsWolfEatTarget($user->id)) return '狼同士には投票できません'; //仲間狼判定
    return null;
  }

  //人狼襲撃処理
  final function WolfEat($skip) {
    $target = $this->GetWolfTarget();
    $target->wolf_eat    = false;
    $target->wolf_killed = false;
    if ($skip || DB::$ROOM->IsQuiz()) return; //スキップモード・クイズ村仕様

    $actor = $this->GetWolfVoter();
    if (! $actor->IsSiriusWolf(false)) { //罠判定 (覚醒天狼は無効)
      foreach (RoleManager::LoadFilter('trap') as $filter) {
	if ($filter->TrapStack($actor, $target->id)) return;
      }
    }
    $this->SetStack($actor, 'voter');

    //逃亡者の巻き添え判定
    foreach (array_keys(RoleManager::GetStack('escaper'), $target->id) as $id) {
      DB::$USER->Kill($id, 'WOLF_KILLED'); //死亡処理
    }

    //護衛判定
    if (DB::$ROOM->date > 1 && ! $actor->IsSiriusWolf() &&
	RoleManager::GetClass('guard')->Guard($target)) {
      //RoleManager::p('guard_success', 'GuardSuccess');
      RoleManager::LoadMain($actor)->GuardCounter();
      return;
    }
    if ($this->IgnoreWolfEat()) return; //襲撃耐性判定

    //襲撃処理
    $wolf_filter = RoleManager::LoadMain($actor);
    $wolf_filter->WolfKill($target);
    $target->wolf_eat    = true;
    $target->wolf_killed = true;
    if (! $target->IsPoison() || $actor->IsSiriusWolf()) return; //毒死判定

    $poison_target = $wolf_filter->GetPoisonEatTarget(); //対象選出
    if ($poison_target->IsChallengeLovers()) return; //難題なら無効

    RoleManager::SetActor($target); //襲撃毒死回避判定
    foreach (RoleManager::Load('avoid_poison_eat') as $filter) {
      if ($filter->AvoidPoisonEat($poison_target)) return;
    }
    RoleManager::LoadMain($poison_target)->PoisonDead(); //毒死処理
  }

  //護衛カウンター
  function GuardCounter() {}

  //人狼襲撃耐性判定
  final function IgnoreWolfEat() {
    $target = $this->GetWolfTarget();
    if ($target->IsDummyBoy()) return false; //身代わり君は対象外

    $actor = $this->GetWolfVoter();
    if (! $actor->IsSiriusWolf()) { //特殊襲撃失敗判定 (サブの判定が先/完全覚醒天狼は無効)
      RoleManager::SetActor($target);
      foreach (RoleManager::Load('wolf_eat_resist') as $filter) {
	if ($filter->WolfEatResist()) return true;
      }
      //確率無効タイプ (鬼陣営)
      if ($target->IsOgre() && RoleManager::LoadMain($target)->WolfEatResist()) return true;
    }
    if (DB::$ROOM->date > 1 && $target->IsMainGroup('escaper')) return true; //逃亡者系判定

    $wolf_filter = RoleManager::LoadMain($actor);
    if ($wolf_filter->WolfEatSkip($target)) return true; //人狼襲撃失敗判定
    if ($actor->IsSiriusWolf()) return false; //特殊能力者の処理 (完全覚醒天狼は無効)

    RoleManager::SetActor($target); //人狼襲撃得票カウンター + 身代わり能力者処理
    foreach (RoleManager::Load('wolf_eat_reaction') as $filter) {
      if ($filter->WolfEatReaction()) return true;
    }
    if ($wolf_filter->WolfEatAction($target)) return true; //人狼襲撃能力処理

    RoleManager::SetActor($target);  //人狼襲撃カウンター処理
    foreach (RoleManager::Load('wolf_eat_counter') as $filter) {
      $filter->WolfEatCounter($actor);
    }
    return false;
  }

  //人狼襲撃失敗判定
  function WolfEatSkip(User $user) {
    if ($user->IsWolf()) { //人狼系判定 (例：銀狼出現)
      $this->WolfEatSkipAction($user);
      $user->wolf_eat = true; //襲撃は成功扱い
      return true;
    }
    if ($user->IsFox()) { //妖狐判定
      $filter = RoleManager::LoadMain($user);
      if (! $filter->resist_wolf) return false;
      $this->FoxEatAction($user); //妖狐襲撃処理
      $filter->FoxEatCounter($this->GetWolfVoter()); //妖狐襲撃カウンター処理

      //人狼襲撃メッセージを登録
      if (! DB::$ROOM->IsOption('seal_message')) {
	DB::$ROOM->ResultAbility('FOX_EAT', 'targeted', null, $user->id);
      }
      $user->wolf_eat = true; //襲撃は成功扱い
      return true;
    }
    return false;
  }

  //人狼襲撃失敗処理
  protected function WolfEatSkipAction(User $user) {}

  //妖狐襲撃処理
  protected function FoxEatAction(User $user) {}

  //人狼襲撃処理
  function WolfEatAction(User $user) {}

  //人狼襲撃死亡処理
  function WolfKill(User $user) {
    DB::$USER->Kill($user->id, 'WOLF_KILLED');
    $this->WolfKillAction($user);
  }

  //人狼襲撃追加処理
  protected function WolfKillAction(User $user) {}

  //毒対象者選出 (襲撃)
  function GetPoisonEatTarget() {
    return GameConfig::POISON_ONLY_EATER ? $this->GetWolfVoter() :
      DB::$USER->ByID(Lottery::Get(DB::$USER->GetLivingWolves()));
  }

  //毒死処理
  function PoisonDead() { DB::$USER->Kill($this->GetID(), 'POISON_DEAD'); }
}
