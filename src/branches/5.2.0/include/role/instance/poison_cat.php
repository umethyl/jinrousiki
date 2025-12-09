<?php
/*
  ◆猫又 (poison_cat)
  ○仕様
  ・能力結果：蘇生 (天啓封印あり)
  ・蘇生率：25% / 誤爆有り
  ・蘇生制限：なし
  ・蘇生後：なし
*/
class Role_poison_cat extends Role {
  public $mix_in = ['poison'];
  public $action     = VoteAction::REVIVE;
  public $not_action = VoteAction::NOT_REVIVE;
  public $result     = RoleAbility::REVIVE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    return $this->CallParent('IsReviveVote') && false === DB::$ROOM->IsOpenCast();
  }

  //投票能力判定 (蘇生能力者専用)
  protected function IsReviveVote() {
    return true;
  }

  protected function IgnoreResult() {
    return DateBorder::PreThree() || DB::$ROOM->IsOpenCast();
  }

  //蘇生結果表示 (Mixin 用)
  final protected function OutputReviveResult() {
    if ($this->IgnoreResult()) {
      return;
    }
    RoleHTML::OutputResult($this->result);
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::REVIVE;
    RoleHTML::OutputVoteNight(VoteCSS::REVIVE, $str, $this->action, $this->not_action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    //生存者に自動公開判定の結果を見せないために先に個別能力判定を行う
    if (false === $this->CallParent('IsReviveVote')) {
      return $this->CallParent('GetDisabledReviveVoteNightMessage');
    } elseif (DB::$ROOM->IsOpenCast()) {
      return VoteRoleMessage::OPEN_CAST;
    } else {
      return null;
    }
  }

  //夜投票無効メッセージ取得 (蘇生能力者専用)
  protected function GetDisabledReviveVoteNightMessage() {
    return null;
  }

  protected function FixLiveVoteNightIconPath() {
    return true;
  }

  protected function IsVoteNightCheckboxLive($live) {
    return false === $live;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  //蘇生
  final public function Revive(User $user) {
    $target = $this->GetReviveTarget($user);
    $result = ((null === $target) || ! $this->ReviveUser($target)) ? 'failed' : 'success';
    if ($result == 'success') {
      if (false === DB::$ROOM->IsEvent('full_revive')) { //雷雨ならスキップ
	$this->CallParent('ReviveAction');
      }
    } else {
      $target = $user;
      DB::$ROOM->StoreDead(DB::$USER->GetHandleName($target->uname), DeadReason::REVIVE_FAILED);
    }

    //蘇生結果は憑依を追跡しない
    DB::$ROOM->StoreAbility(RoleAbility::REVIVE, $result, $target->handle_name, $this->GetID());
  }

  //蘇生対象者取得
  final protected function GetReviveTarget(User $user) {
    //-- 蘇生データ取得 --//
    if (DB::$ROOM->IsEvent('full_revive')) { //天候
      $event = 100;
    } else {
      $event = DB::$ROOM->IsEvent('no_revive') ? 0 : null;
    }
    $revive = isset($event) ? $event : $this->CallParent('GetReviveRate'); //蘇生率
    if ($this->IsBoostRevive()) {
      $revive = min(100, ceil($revive * 1.3));
    }

    $missfire = isset($event) ? 0 : $this->GetMissfireRate($revive); //誤爆率
    if (DB::$ROOM->IsEvent('missfire_revive')) {
      $missfire *= 2;
    }
    $missfire = min($revive, $missfire); //誤爆率は蘇生率を超えない

    $rand = Lottery::GetPercent(); //蘇生判定用乱数
    //$rand = 5; Lottery::Rand(10); //テスト用
    //Text::p("{$revive} ({$missfire})", "◆Info: {$this->GetUname()} => {$user->uname}");
    //Text::p($rand, sprintf('◆Rate: %s', $this->GetUname()));
    if ($rand > $revive) { //蘇生失敗
      return null;
    }

    if ($rand <= $missfire) { //誤爆蘇生
      $stack = [];
      //現時点の身代わり君と蘇生能力者が選んだ人以外の死者と憑依者を検出
      foreach (DB::$USER->Get() as $target) {
	if ($target->IsDummyBoy() || $target->IsOn(UserMode::REVIVE) || $user->IsSame($target) ||
	    RoleUser::LimitedRevive($target)) {
	  continue;
	}

	if ($target->IsOn(UserMode::DEAD) || ! DB::$USER->IsVirtualLive($target->id, true)) {
	  $stack[] = $target->id;
	}
      }
      //Text::p($stack, '◆Target [Missfire]');
      if (count($stack) > 0) { //候補がいる時だけ入れ替える
	$user = DB::$USER->ByID(Lottery::Get($stack));
      }
    }
    //$target = DB::$USER->ByID(24); //テスト用
    //Text::p($user->uname, '◆ReviveUser');

    //蘇生失敗判定
    if ($this->CallParent('IgnoreReviveTarget', $user) || RoleUser::LimitedRevive($user)) {
      return null;
    } else {
      return $user;
    }
  }

  //蘇生率取得
  protected function GetReviveRate() {
    return 25;
  }

  //蘇生率強化判定
  final protected function IsBoostRevive() {
    $data = 'boost_revive';
    $flag = $this->GetStack($data);
    if (null === $flag) {
      $flag = DB::$USER->IsLiveRole('revive_brownie');
      $this->SetStack($flag, $data);
    }
    return $flag;
  }

  //誤爆率取得
  protected function GetMissfireRate($revive) {
    return floor($revive / 5);
  }

  //蘇生制限対象者判定
  protected function IgnoreReviveTarget(User $user) {
    return false;
  }

  //蘇生実行
  final protected function ReviveUser(User $user) {
    if (RoleUser::IsPossessed($user)) { //憑依能力者対応
      if ($user->IsOn(UserMode::REVIVE)) { //蘇生済みならスキップ
	return true;
      }

      $virtual = $user->GetVirtual();
      if ($user->IsDead()) { //確定死者
	if (! $user->IsSame($virtual)) { //憑依後に死亡していた場合はリセット処理を行う
	  $user->ReturnPossessed('possessed_target');

	  //憑依先が他の憑依能力者に憑依されていないのならリセット処理を行う
	  $stack = $virtual->GetPartner('possessed');
	  if ($user->id == ArrayFilter::GetMaxKey($stack)) {
	    $virtual->ReturnPossessed('possessed');
	  }
	}
      } elseif ($user->IsLive(true)) { //生存者 (憑依状態確定)
	if (RoleUser::LimitedRevive($virtual)) { //蘇生制限判定
	  return false;
	}

	//見かけ上の蘇生処理
	$user->ReturnPossessed('possessed_target');
	DB::$ROOM->StoreDead($user->handle_name, DeadReason::REVIVE_SUCCESS);

	//本当の死者の蘇生処理
	$virtual->Revive(true);
	$virtual->ReturnPossessed('possessed');

	$this->RevivePossessedCancel($user); //憑依キャンセル判定
	return true;
      } else { //当夜に死んだケース
	if (! $user->IsSame($virtual)) { //憑依中ならリセット
	  $user->ReturnPossessed('possessed_target'); //本人
	  $virtual->ReturnPossessed('possessed'); //憑依先
	}
	$this->RevivePossessedCancel($user); //憑依キャンセル判定
      }
    } else {
      $name = RoleVoteTarget::GRAVE;
      if ($this->InStack($user->id, $name)) { //死者妨害判定
	//Text::p($this->GetActor()->uname, "◆Target [{$name}/{$this->role}]");
	foreach (RoleLoader::LoadFilter($name) as $filter) {
	  $filter->Grave($this->GetActor());
	}
      }

      if ($user->IsOn(UserMode::REVIVE)) { //蘇生済みならスキップ
	return true;
      }

      if (! $user->IsSame(DB::$USER->ByReal($user->id))) { //憑依されていたらリセット
	$user->ReturnPossessed('possessed');
      }
    }
    $user->Revive(); //蘇生処理
    return true;
  }

  //憑依キャンセル
  final protected function RevivePossessedCancel(User $user) {
    if (RoleUser::IsPossessedTarget($user)) {
      $user->Flag()->Off(UserMode::POSSESSED_RESET);
      $user->Flag()->On(UserMode::POSSESSED_CANCEL);
    }
  }

  //蘇生後処理
  protected function ReviveAction() {}
}
