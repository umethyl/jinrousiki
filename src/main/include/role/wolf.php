<?php
/*
  ◆人狼 (wolf)
  ○仕様
  ・遠吠え：通常
  ・仲間表示：人狼枠(憑依追跡)・囁き狂人・無意識枠
  ・仲間襲撃：不可
  ・護衛カウンター：なし
  ・襲撃失敗判定：人狼系・妖狐
  ・襲撃失敗：なし
  ・妖狐襲撃：なし
  ・人狼襲撃死因：人狼襲撃
  ・襲撃追加：なし
  ・毒対象選出 (襲撃)：通常
  ・毒死：通常
*/
class Role_wolf extends Role {
  public $action = VoteAction::WOLF;

  protected function GetPartner() {
    $stack = $this->GetWolfPartner();
    if (! RoleUser::IsWolf($this->GetActor())) {
      unset($stack['wolf_partner']);
      unset($stack['mad_partner']);
    }
    if (! DB::$ROOM->IsNight()) {
      unset($stack['unconscious_list']);
    }
    return $stack;
  }

  //仲間表示 (人狼用)
  final protected function GetWolfPartner() {
    $main = 'wolf_partner';     //人狼
    $mad  = 'mad_partner';      //囁き狂人
    $sub  = 'unconscious_list'; //無意識
    $stack = array($main => array(), $mad => array(), $sub => array());
    foreach (DB::$USER->Get() as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsRole('possessed_wolf')) {
	$stack[$main][] = $user->GetName(); //憑依追跡
      } elseif (RoleUser::IsWolf($user)) {
	$stack[$main][] = $user->handle_name;
      } elseif ($user->IsRole('whisper_mad')) {
	$stack[$mad][] = $user->handle_name;
      } elseif ($user->IsRole('unconscious') || $user->IsRoleGroup('scarlet')) {
	$stack[$sub][] = $user->handle_name;
      }
    }
    return $stack;
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::WOLF, RoleAbilityMessage::WOLF, $this->action);
  }

  //遠吠え
  public function Howl(TalkBuilder $builder, TalkParser $talk) {
    if (! $builder->flag->wolf_howl) return false; //スキップ判定

    $str   = RoleTalkMessage::WOLF_HOWL;
    $voice = $talk->font_type;
    foreach ($builder->filter as $filter) {
      $filter->FilterWhisper($voice, $str); //フィルタリング処理
    }

    $stack = array(
      'str'       => $str,
      'symbol'    => '',
      'user_info' => RoleTalkMessage::WOLF,
      'voice'     => $voice,
      'talk_id'   => $builder->GetTalkID($talk)
    );
    return $builder->Register($stack);
  }

  protected function GetVoteTargetUserFilter(array $list) {
    if ($this->IsFixDummyBoy()) {
      $id = DB::$USER->GetDummyBoyID();
      return array($id => $list[$id]);
    } else {
      return $list;
    }
  }

  //身代わり君襲撃固定判定
  final protected function IsFixDummyBoy() {
    return DB::$ROOM->IsQuiz() || (DB::$ROOM->IsDummyBoy() && DB::$ROOM->IsDate(1));
  }

  protected function GetPartnerVoteIconPath(User $user) {
    return $this->IsWolfPartner($user->id) ? Icon::GetWolf() : null;
  }

  //仲間狼判定
  protected function IsWolfPartner($id) {
    return RoleUser::IsWolf(DB::$USER->ByReal($id));
  }

  protected function IsVoteCheckboxFilter(User $user) {
    return $this->IsWolfEatTarget($user->id);
  }

  //仲間狼襲撃可能判定
  protected function IsWolfEatTarget($id) {
    return ! $this->IsWolfPartner($id);
  }

  protected function IsVoteCheckboxChecked(User $user) {
    return $this->IsFixDummyBoy() && $user->IsDummyBoy();
  }

  protected function ExistsAction(array $list) {
    foreach (DB::$USER->SearchLiveWolf() as $id) {
      if (RoleLoader::LoadMain(DB::$USER->ByID($id))->ExistsSelfAction($list)) {
	return true;
      }
    }
    return false;
  }

  protected function IgnoreVoteNightFilter(User $user) {
    //身代わり君判定 (クイズ村 > 身代わり君)
    if (DB::$ROOM->IsQuiz()) {
      if (! $user->IsDummyBoy()) return VoteRoleMessage::TARGET_QUIZ;
    } elseif (DB::$ROOM->IsDummyBoy() && DB::$ROOM->IsDate(1)) {
      if (! $user->IsDummyBoy()) return VoteRoleMessage::TARGET_ONLY_DUMMY_BOY;
    }
    return $this->IsWolfEatTarget($user->id) ? null : VoteRoleMessage::TARGET_WOLF; //仲間狼判定
  }

  //人狼襲撃処理
  final public function WolfEat() {
    $target = $this->GetWolfTarget();
    $target->wolf_eat    = false;
    $target->wolf_killed = false;
    if (RoleManager::Stack()->Get('skip') || DB::$ROOM->IsQuiz()) return; //スキップ判定

    $actor = $this->GetWolfVoter();
    if (! RoleUser::IsSiriusWolf($actor, false)) { //罠判定 (覚醒天狼は無効)
      foreach (RoleLoader::LoadFilter('trap') as $filter) {
	if ($filter->TrapComposite($actor, $target->id)) {
	  return $this->WolfEatFailed('TRAP');
	}
      }
    }
    $this->SetStack($actor, 'voter');

    //逃亡者の巻き添え判定
    foreach (RoleManager::Stack()->GetKeyList(RoleVoteTarget::ESCAPER, $target->id) as $id) {
      DB::$USER->Kill($id, DeadReason::WOLF_KILLED); //死亡処理
    }

    //護衛判定 (護衛能力判定 > 天狼判定)
    if (DB::$ROOM->date > 1 && RoleUser::Guard($target) && ! RoleUser::IsSiriusWolf($actor)) {
      //RoleManager::Stack()->p(RoleVoteSuccess::GUARD, '◆GuardSuccess');
      RoleLoader::LoadMain($actor)->GuardCounter();
      return $this->WolfEatFailed('GUARD');
    }
    if ($this->IgnoreWolfEat()) return; //襲撃耐性判定

    //襲撃処理
    $wolf_filter = RoleLoader::LoadMain($actor);
    $wolf_filter->WolfKill($target);
    $target->wolf_eat    = true;
    $target->wolf_killed = true;
    if (RoleUser::IsPoison($target) && ! RoleUser::IsSiriusWolf($actor)) { //毒死判定 (天狼は無効)
      $poison_target = $wolf_filter->GetPoisonEatTarget(); //対象選出
      if (RoleUser::IsAvoidLovers($poison_target)) return; //特殊耐性恋人なら無効

      //襲撃毒死回避判定
      foreach (RoleLoader::LoadUser($target, 'avoid_poison_eat') as $filter) {
	if ($filter->IgnorePoisonEat($poison_target)) return;
      }
      RoleLoader::LoadMain($poison_target)->PoisonDead(); //毒死処理
    }
  }

  //護衛カウンター
  public function GuardCounter() {}

  //人狼襲撃耐性判定
  final protected function IgnoreWolfEat() {
    $target = $this->GetWolfTarget();
    if ($target->IsDummyBoy()) { //身代わり君は専用判定後スキップ
      //身代わり君人狼襲撃カウンター処理
      foreach (RoleLoader::LoadUser($target, 'wolf_eat_dummy_boy') as $filter) {
	$filter->WolfEatDummyBoyCounter();
      }
      return false;
    }

    $actor = $this->GetWolfVoter();
    if (! RoleUser::IsSiriusWolf($actor)) { //特殊襲撃失敗判定 (サブの判定が先/完全覚醒天狼は無効)
      foreach (RoleLoader::LoadUser($target, 'wolf_eat_resist') as $filter) {
	if ($filter->WolfEatResist()) {
	  return $this->WolfEatFailed('RESIST', true);
	}
      }

      //確率無効タイプ (鬼陣営)
      if ($target->IsMainCamp(Camp::OGRE) && RoleLoader::LoadMain($target)->WolfEatResist()) {
	return $this->WolfEatFailed('OGRE', true);
      }
    }

    if (DB::$ROOM->date > 1) {
      if (RoleUser::IsExit($target)) { //離脱判定
	return $this->WolfEatFailed('EXIT', true);
      }
      if (RoleUser::IsEscape($target)) { //逃亡判定
	return $this->WolfEatFailed('ESCAPER', true);
      }
    }

    $wolf_filter = RoleLoader::LoadMain($actor);
    if ($wolf_filter->WolfEatSkip($target)) return true; //人狼襲撃失敗判定

    if (! RoleUser::IsSiriusWolf($actor)) { //特殊能力者の処理 (完全覚醒天狼は無効)
      //人狼襲撃得票カウンター + 身代わり能力者処理
      foreach (RoleLoader::LoadUser($target, 'wolf_eat_reaction') as $filter) {
	if ($filter->WolfEatReaction()) {
	  return $this->WolfEatFailed('REACTION', true);
	}
      }

      if ($wolf_filter->WolfEatAction($target)) { //人狼襲撃能力処理
	return $this->WolfEatFailed('ACTION', true);
      }

      //人狼襲撃カウンター処理
      foreach (RoleLoader::LoadUser($target, 'wolf_eat_counter') as $filter) {
	$filter->WolfEatCounter($actor);
      }
    }
    return false;
  }

  //人狼襲撃失敗判定
  final public function WolfEatSkip(User $user) {
    if ($this->IgnoreWolfEatSkip()) return false; //スキップ判定

    if ($user->IsMainGroup(CampGroup::WOLF)) { //人狼系判定 (例：銀狼出現)
      $this->WolfEatSkipAction($user);
      $user->wolf_eat = true; //襲撃は成功扱い
      return $this->WolfEatFailed('WOLF', true);
    }

    if (RoleUser::IsFoxCount($user)) { //妖狐判定
      $filter = RoleLoader::LoadMain($user);
      if (! $filter->IsResistWolf()) return false;
      $this->FoxEatAction($user); //妖狐襲撃処理
      $filter->FoxEatCounter($this->GetWolfVoter()); //妖狐襲撃カウンター処理

      //人狼襲撃メッセージを登録
      if (! DB::$ROOM->IsOption('seal_message')) {
	DB::$ROOM->ResultAbility(RoleAbility::FOX, 'targeted', null, $user->id);
      }
      $user->wolf_eat = true; //襲撃は成功扱い
      return $this->WolfEatFailed('FOX', true);
    }
    return false;
  }

  //人狼襲撃失敗判定スキップ判定
  protected function IgnoreWolfEatSkip() {
    return false;
  }

  //人狼襲撃失敗処理
  protected function WolfEatSkipAction(User $user) {}

  //妖狐襲撃処理
  protected function FoxEatAction(User $user) {}

  //人狼襲撃処理
  public function WolfEatAction(User $user) {}

  //人狼襲撃死亡処理
  final public function WolfKill(User $user) {
    if ($this->IgnoreWolfKill($user)) return;
    DB::$USER->Kill($user->id, $this->GetWolfKillReason());
    $this->WolfKillAction($user);
  }

  //人狼襲撃死亡処理スキップ判定
  protected function IgnoreWolfKill(User $user) {
    return false;
  }

  //人狼襲撃死因取得
  protected function GetWolfKillReason() {
    return DeadReason::WOLF_KILLED;
  }

  //人狼襲撃追加処理
  protected function WolfKillAction(User $user) {}

  //毒対象者選出 (襲撃)
  public function GetPoisonEatTarget() {
    if (GameConfig::POISON_ONLY_EATER) {
      return $this->GetWolfVoter();
    } else {
      return DB::$USER->ByID(Lottery::Get(DB::$USER->SearchLiveWolf()));
    }
  }

  //毒死処理
  final public function PoisonDead() {
    if ($this->IgnorePoisonDead()) return;
    DB::$USER->Kill($this->GetID(), DeadReason::POISON_DEAD);
  }

  //毒死回避判定
  protected function IgnorePoisonDead() {
    return false;
  }

  //人狼襲撃失敗ログ出力
  private function WolfEatFailed($type, $bool = false) {
    DB::$ROOM->ResultDead(null, 'WOLF_FAILED', $type);
    return $bool;
  }
}
