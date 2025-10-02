<?php
/*
  ◆猫又 (poison_cat)
  ○仕様
  ・蘇生率：25% / 誤爆有り
  ・蘇生後：なし
*/
class Role_poison_cat extends Role {
  public $mix_in = array('poison');
  public $action     = 'POISON_CAT_DO';
  public $not_action = 'POISON_CAT_NOT_DO';
  public $result     = 'POISON_CAT_RESULT';
  public $action_date_type = 'after';
  public $submit      = 'revive_do';
  public $not_submit  = 'revive_not_do';
  public $revive_rate = 25;
  public $missfire_rate;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || DB::$ROOM->IsOpenCast() || DB::$ROOM->IsOption('seal_message');
  }

  //蘇生結果表示 (Mixin 用)
  final public function OutputReviveResult() {
    if ($this->IgnoreResult()) return;
    $this->OutputAbilityResult($this->result);
  }

  public function OutputAction() {
    RoleHTML::OutputVote('revive-do', $this->submit, $this->action, $this->not_action);
  }

  protected function IsAddVote() {
    return ! DB::$ROOM->IsOpenCast() && $this->CallParent('IsReviveVote');
  }

  //投票能力判定 (蘇生能力者専用)
  public function IsReviveVote() {
    return true;
  }

  protected function IgnoreVoteFilter() {
    if (DB::$ROOM->IsOpenCast()) return VoteRoleMessage::OPEN_CAST;
    return $this->CallParent('IgnoreReviveVoteFilter');
  }

  //投票スキップ追加判定 (蘇生能力者専用)
  public function IgnoreReviveVoteFilter() {
    return null;
  }

  public function GetVoteIconPath(User $user, $live) {
    return Icon::GetFile($user->icon_filename);
  }

  public function IsVoteCheckbox(User $user, $live) {
    return ! $live && ! $this->IsActor($user) && ! $user->IsDummyBoy();
  }

  public function IgnoreVoteNight(User $user, $live) {
    return $live ? VoteRoleMessage::TARGET_ALIVE : null;
  }

  //蘇生
  final public function Revive(User $user) {
    $target = $this->GetReviveTarget($user);
    $result = is_null($target) || ! $this->ReviveUser($target) ? 'failed' : 'success';
    if ($result == 'success') {
      //雷雨ならスキップ
      if (! DB::$ROOM->IsEvent('full_revive')) $this->CallParent('ReviveAction');
    }
    else {
      $target = $user;
      DB::$ROOM->ResultDead(DB::$USER->GetHandleName($target->uname), 'REVIVE_FAILED');
    }
    if (DB::$ROOM->IsOption('seal_message')) return; //蘇生結果を登録 (天啓封印ならスキップ)

    //蘇生結果は憑依を追跡しない
    DB::$ROOM->ResultAbility('POISON_CAT_RESULT', $result, $target->handle_name, $this->GetID());
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
    if ($this->IsBoostRevive()) $revive = min(100, ceil($revive * 1.3));

    if (isset($event)) { //誤爆率
      $missfire = 0;
    } else {
      $missfire = isset($this->missfire_rate) ? $this->missfire_rate : floor($revive / 5);
    }
    if (DB::$ROOM->IsEvent('missfire_revive')) $missfire *= 2;
    if ($missfire > $revive) $missfire = $revive;

    $rand = Lottery::GetPercent(); //蘇生判定用乱数
    //$rand = 5; Lottery::Rand(10); //テスト用
    //Text::p("{$revive} ({$missfire})", "◆Info: {$this->GetUname()} => {$user->uname}");
    //Text::p($rand, sprintf('◆Rate: %s', $this->GetUname()));

    if ($rand > $revive) return null; //蘇生失敗
    if ($rand <= $missfire) { //誤爆蘇生
      $stack = array();
      //現時点の身代わり君と蘇生能力者が選んだ人以外の死者と憑依者を検出
      foreach (DB::$USER->rows as $target) {
	if ($target->IsDummyBoy() || $target->revive_flag || $user->IsSame($target) ||
	    $target->IsReviveLimited()) {
	  continue;
	}

	if ($target->dead_flag || ! DB::$USER->IsVirtualLive($target->id, true)) {
	  $stack[] = $target->id;
	}
      }
      //Text::p($stack, '◆Target [Missfire]');
      //候補がいる時だけ入れ替える
      if (count($stack) > 0) $user = DB::$USER->ByID(Lottery::Get($stack));
    }
    //$target = DB::$USER->ByID(24); //テスト用
    //Text::p($user->uname, '◆ReviveUser');
    $class = $this->GetParent($method = 'IgnoreReviveTarget');
    return $class->$method($user) || $user->IsReviveLimited() ? null : $user; //蘇生失敗判定
  }

  //蘇生率取得
  public function GetReviveRate() {
    return $this->revive_rate;
  }

  //蘇生率強化判定
  protected function IsBoostRevive() {
    $data = 'boost_revive';
    if (is_null($flag = $this->GetStack($data))) {
      $flag = DB::$USER->IsLiveRole('revive_brownie');
      $this->SetStack($flag, $data);
    }
    return $flag;
  }

  //蘇生制限対象者判定
  public function IgnoreReviveTarget(User $user) {
    return false;
  }

  //蘇生実行
  protected function ReviveUser(User $user) {
    if ($user->IsPossessedGroup()) { //憑依能力者対応
      if ($user->revive_flag) return true; //蘇生済みならスキップ

      $virtual = $user->GetVirtual();
      if ($user->IsDead()) { //確定死者
	if (! $user->IsSame($virtual)) { //憑依後に死亡していた場合はリセット処理を行う
	  $user->ReturnPossessed('possessed_target');

	  //憑依先が他の憑依能力者に憑依されていないのならリセット処理を行う
	  $stack = $virtual->GetPartner('possessed');
	  if ($user->id == $stack[max(array_keys($stack))]) {
	    $virtual->ReturnPossessed('possessed');
	  }
	}
      }
      elseif ($user->IsLive(true)) { //生存者 (憑依状態確定)
	if ($virtual->IsDrop()) return false; //蘇生辞退者対応

	//見かけ上の蘇生処理
	$user->ReturnPossessed('possessed_target');
	DB::$ROOM->ResultDead($user->handle_name, 'REVIVE_SUCCESS');

	//本当の死者の蘇生処理
	$virtual->Revive(true);
	$virtual->ReturnPossessed('possessed');

	//憑依予定者が居たらキャンセル
	if (array_key_exists($user->id, $this->GetStack('possessed'))) {
	  $user->possessed_reset  = false;
	  $user->possessed_cancel = true;
	}
	return true;
      }
      else { //当夜に死んだケース
	if (! $user->IsSame($virtual)) { //憑依中ならリセット
	  $user->ReturnPossessed('possessed_target'); //本人
	  $virtual->ReturnPossessed('possessed'); //憑依先
	}

	//憑依予定者が居たらキャンセル
	if (array_key_exists($user->id, $this->GetStack('possessed'))) {
	  $user->possessed_reset  = false;
	  $user->possessed_cancel = true;
	}
      }
    }
    elseif (! $user->IsSame(DB::$USER->ByReal($user->id))) { //憑依されていたらリセット
      $user->ReturnPossessed('possessed');
    }
    $user->Revive(); //蘇生処理
    return true;
  }

  //蘇生後処理
  public function ReviveAction() {}
}
