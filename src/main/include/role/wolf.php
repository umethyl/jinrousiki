<?php
/*
  ◆人狼 (wolf)
  ○仕様
  ・遠吠え：通常
  ・仲間表示：人狼枠(憑依追跡)・囁き狂人・無意識枠
  ・仲間襲撃：不可
  ・罠：有効
  ・護衛：有効
  ・護衛カウンター：なし
  ・襲撃失敗判定：人狼系・妖狐 (天啓封印あり)
  ・襲撃失敗：なし
  ・妖狐襲撃：なし
  ・襲撃死因：人狼襲撃
  ・襲撃追加：なし
  ・襲撃毒発動：有効
  ・襲撃毒対象選出：通常
  ・毒死：通常
*/
class Role_wolf extends Role {
  public $action = VoteAction::WOLF;

  protected function GetPartner() {
    $stack = $this->GetWolfPartner();
    if (false === RoleUser::IsWolf($this->GetActor())) {
      unset($stack['wolf_partner']);
      unset($stack['mad_partner']);
    }
    if (false === DB::$ROOM->IsNight()) {
      unset($stack['unconscious_list']);
    }
    return $stack;
  }

  //仲間表示 (人狼用)
  final protected function GetWolfPartner() {
    $main = 'wolf_partner';     //人狼
    $mad  = 'mad_partner';      //囁き狂人
    $sub  = 'unconscious_list'; //無意識
    $stack = [$main => [], $mad => [], $sub => []];
    foreach (DB::$USER->Get() as $user) {
      if ($this->IsActor($user)) {
	continue;
      }

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
    RoleHTML::OutputVoteNight(VoteCSS::WOLF, RoleAbilityMessage::WOLF, $this->action);
  }

  //遠吠え
  public function Howl(TalkBuilder $builder, TalkParser $talk) {
    if (false === $builder->flag->wolf_howl) { //スキップ判定
      return false;
    }

    $sentence = RoleTalkMessage::WOLF_HOWL;
    $voice    = $talk->font_type;
    foreach ($builder->filter as $filter) {
      $filter->FilterWhisper($voice, $sentence); //フィルタリング処理
    }

    $stack = [
      TalkElement::ID       => $builder->GetTalkID($talk),
      TalkElement::SYMBOL   => '',
      TalkElement::NAME     => RoleTalkMessage::WOLF,
      TalkElement::VOICE    => $voice,
      TalkElement::SENTENCE => $sentence
    ];
    return $builder->Register($stack);
  }

  protected function GetVoteNightTargetUserFilter(array $list) {
    if ($this->FixDummyBoy()) {
      $id = DB::$USER->GetDummyBoyID();
      return [$id => $list[$id]];
    } else {
      return $list;
    }
  }

  //身代わり君襲撃固定判定
  final protected function FixDummyBoy() {
    return DB::$ROOM->IsQuiz() || (DB::$ROOM->IsDummyBoy() && DB::$ROOM->IsDate(1));
  }

  protected function GetPartnerVoteNightIconPath(User $user) {
    return $this->IsWolfPartner($user->id) ? Icon::GetWolf() : null;
  }

  //仲間狼判定
  protected function IsWolfPartner($id) {
    return RoleUser::IsWolf(DB::$USER->ByReal($id));
  }

  protected function IsVoteNightCheckboxFilter(User $user) {
    return $this->IsWolfEatTarget($user->id);
  }

  //仲間狼襲撃可能判定
  protected function IsWolfEatTarget($id) {
    return false === $this->IsWolfPartner($id);
  }

  protected function CheckedVoteNightCheckbox(User $user) {
    return $this->FixDummyBoy() && $user->IsDummyBoy();
  }

  protected function ExistsAction(array $list) {
    foreach (DB::$USER->SearchLiveWolf() as $id) {
      if (RoleLoader::LoadMain(DB::$USER->ByID($id))->ExistsSelfAction($list)) {
	return true;
      }
    }
    return false;
  }

  protected function ValidateVoteNightTargetFilter(User $user) {
    //身代わり君判定 (クイズ村 > 身代わり君) > 仲間狼判定
    if ($this->FixDummyBoy() && false === $user->IsDummyBoy()) {
      if (DB::$ROOM->IsQuiz()) {
	throw new UnexpectedValueException(VoteRoleMessage::TARGET_QUIZ);
      } else {
	throw new UnexpectedValueException(VoteRoleMessage::TARGET_ONLY_DUMMY_BOY);
      }
    } elseif (false === $this->IsWolfEatTarget($user->id)) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_WOLF);
    }
  }

  //人狼襲撃情報登録
  final public function SetWolf($id) {
    $this->SetWolfStack($this->GetActor(), DB::$USER->ByID($this->GetWolfTargetID($id)));
  }

  //人狼襲撃情報登録 (スキップ対応)
  final public function SetSkipWolf() {
    $user = new User();
    $this->SetWolfStack($user, $user);
  }

  //人狼襲撃対象者ID取得
  protected function GetWolfTargetID($id) {
    return $id;
  }

  //人狼襲撃処理
  final public function WolfEat() {
    $target = $this->GetWolfTarget();
    $target->wolf_eat    = false;
    $target->wolf_killed = false;
    if (RoleManager::Stack()->Get('skip') || DB::$ROOM->IsQuiz()) { //スキップ判定
      return;
    }

    $actor = $this->GetWolfVoter();
    $wolf_filter = RoleLoader::LoadMain($actor);
    if ($wolf_filter->EnableTrap($actor)) { //罠有効判定
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

    //護衛判定 (護衛能力判定 > 護衛有効判定)
    if (DB::$ROOM->date > 1 && RoleUser::Guard($target) && $wolf_filter->EnableGuard($actor)) {
      //RoleManager::Stack()->p(RoleVoteSuccess::GUARD, '◆GuardSuccess');
      RoleLoader::LoadMain($actor)->GuardCounter();
      return $this->WolfEatFailed('GUARD');
    }
    if ($this->IgnoreWolfEat()) { //襲撃失敗判定
      return;
    }

    //襲撃処理
    $wolf_filter->WolfKill($target);
    $target->wolf_eat    = true;
    $target->wolf_killed = true;
    if (RoleUser::IsPoison($target) && $wolf_filter->EnablePoisonEat($actor)) {
      $poison_target = $wolf_filter->GetPoisonEatTarget(); //対象選出
      if (RoleUser::AvoidLovers($poison_target)) { //特殊耐性恋人なら無効
	return;
      }

      //襲撃毒死回避判定
      foreach (RoleLoader::LoadUser($target, 'avoid_poison_eat') as $filter) {
	if ($filter->IgnorePoisonEat($poison_target)) {
	  return;
	}
      }
      RoleLoader::LoadMain($poison_target)->PoisonDead(); //毒死処理
    }
  }

  //罠有効判定
  public function EnableTrap(User $user) {
    return true;
  }

  //護衛有効判定
  public function EnableGuard(User $user) {
    return true;
  }

  //護衛カウンター
  public function GuardCounter() {}

  //人狼襲撃失敗判定
  final protected function IgnoreWolfEat() {
    $target = $this->GetWolfTarget();
    if ($target->IsDummyBoy()) { //身代わり君は専用判定後スキップ
      //身代わり君人狼襲撃カウンター処理
      foreach (RoleLoader::LoadUser($target, 'wolf_eat_dummy_boy') as $filter) {
	$filter->WolfEatDummyBoyCounter();
      }
      return false;
    }

    //襲撃耐性判定 (サブの判定が先/完全覚醒天狼は無効)
    $actor = $this->GetWolfVoter();
    if (false === RoleUser::IsSiriusWolf($actor)) {
      foreach (RoleLoader::LoadUser($target, 'wolf_eat_resist') as $filter) {
	if ($filter->ResistWolfEat()) {
	  return $this->WolfEatFailed('RESIST', true);
	}
      }

      if ($target->IsMainCamp(Camp::OGRE)) { //確率無効タイプ (鬼陣営)
	if (RoleLoader::LoadMain($target)->ResistWolfEat()) {
	  return $this->WolfEatFailed('OGRE', true);
	}
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
    if ($wolf_filter->DisableWolfEat($target)) { //人狼襲撃無効判定
      return true;
    }

    if (false === RoleUser::IsSiriusWolf($actor)) { //特殊能力者の処理 (完全覚醒天狼は無効)
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

  //人狼襲撃無効判定 (スキップ判定 > 人狼襲撃 > 妖狐襲撃 > 襲撃成功)
  final public function DisableWolfEat(User $user) {
    if ($this->IgnoreDisableWolfEat()) {
      return false;
    } elseif ($user->IsMainGroup(CampGroup::WOLF)) { //人狼系判定 (例：銀狼襲撃)
      $this->WolfEatWolfAction($user);
      $user->wolf_eat = true; //襲撃は成功扱い
      return $this->WolfEatFailed('WOLF', true);
    } elseif (RoleUser::IsFoxCount($user)) { //妖狐判定
      $filter = RoleLoader::LoadMain($user);
      if (false === $filter->ResistWolfEatFox()) { //妖狐人狼襲撃耐性判定
	return false;
      }
      $this->WolfEatFoxAction($user); //妖狐襲撃処理
      $filter->WolfEatFoxCounter($this->GetWolfVoter()); //妖狐襲撃カウンター処理

      //人狼襲撃メッセージを登録 (天啓封印あり)
      DB::$ROOM->StoreAbility(RoleAbility::FOX, 'targeted', null, $user->id);
      $user->wolf_eat = true; //襲撃は成功扱い
      return $this->WolfEatFailed('FOX', true);
    } else {
      return false;
    }
  }

  //人狼襲撃無効判定スキップ判定
  protected function IgnoreDisableWolfEat() {
    return false;
  }

  //仲間人狼襲撃処理
  protected function WolfEatWolfAction(User $user) {}

  //妖狐襲撃処理
  protected function WolfEatFoxAction(User $user) {}

  //人狼襲撃処理
  public function WolfEatAction(User $user) {}

  //人狼襲撃死亡処理
  final public function WolfKill(User $user) {
    if ($this->IgnoreWolfKill($user)) {
      return;
    }

    //死因取得 (身代わり君襲撃時は固定)
    if ($user->IsDummyBoy()) {
      $reason = DeadReason::WOLF_KILLED;
    } else {
      $reason = $this->GetWolfKillReason();
    }
    DB::$USER->Kill($user->id, $reason);
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

  //人狼襲撃毒有効判定
  public function EnablePoisonEat(User $user) {
    return true;
  }

  //人狼襲撃毒対象者選出
  public function GetPoisonEatTarget() {
    if (GameConfig::POISON_ONLY_EATER) {
      return $this->GetWolfVoter();
    } else {
      return DB::$USER->ByID(Lottery::Get(DB::$USER->SearchLiveWolf()));
    }
  }

  //毒死処理
  final public function PoisonDead() {
    if ($this->IgnorePoisonDead()) {
      return;
    }
    DB::$USER->Kill($this->GetID(), DeadReason::POISON_DEAD);
  }

  //毒死回避判定
  protected function IgnorePoisonDead() {
    return false;
  }

  //人狼襲撃情報 Stack 登録
  private function SetWolfStack(User $actor, User $target) {
    $this->SetStack($actor, 'voted_wolf');
    $this->SetStack($target, 'wolf_target');
  }

  //人狼襲撃失敗ログ出力
  private function WolfEatFailed($type, $bool = false) {
    DB::$ROOM->StoreDead(null, 'WOLF_FAILED', $type);
    return $bool;
  }
}
