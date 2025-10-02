<?php
/*
  ◆猫又 (poison_cat)
  ○仕様
  ・蘇生率：25% / 誤爆有り
  ・蘇生後：なし
*/
class Role_poison_cat extends Role {
  public $mix_in = 'poison';
  public $action      = 'POISON_CAT_DO';
  public $not_action  = 'POISON_CAT_NOT_DO';
  public $submit      = 'revive_do';
  public $not_submit  = 'revive_not_do';
  public $revive_rate = 25;
  public $missfire_rate;

  //Mixin あり
  function OutputResult() {
    if (DB::$ROOM->date > 2 && ! DB::$ROOM->IsOpenCast() && ! DB::$ROOM->IsOption('seal_message')) {
      $this->OutputAbilityResult('POISON_CAT_RESULT');
    }
  }

  function OutputAction() {
    RoleHTML::OutputVote('revive-do', $this->submit, $this->action, $this->not_action);
  }

  function IsVote() { return DB::$ROOM->date > 1 && ! DB::$ROOM->IsOpenCast(); }

  function GetIgnoreMessage() { return '初日は蘇生できません'; }

  function IgnoreVoteFilter() {
    if (DB::$ROOM->IsOpenCast()) {
      return '「霊界で配役を公開しない」オプションがオフの時は投票できません';
    }
    $self  = 'Role_' . $this->role;
    $class = $this->GetClass($method = 'IgnoreVoteAction');
    return method_exists($self, $method) ? $class->$method() : null;
  }

  function GetVoteIconPath(User $user, $live) { return Icon::GetFile($user->icon_filename); }

  function IsVoteCheckbox(User $user, $live) {
    return ! $live && ! $this->IsActor($user) && ! $user->IsDummyBoy();
  }

  function IgnoreVoteNight(User $user, $live) {
    return $live ? '死者以外には投票できません' : null;
  }

  //蘇生
  function Revive(User $user) {
    $target = $this->GetReviveTarget($user);
    $result = is_null($target) || ! $this->ReviveUser($target) ? 'failed' : 'success';
    if ($result == 'success') {
      if (! DB::$ROOM->IsEvent('full_revive')) { //雷雨ならスキップ
	$class = $this->GetClass($method = 'ReviveAction');
	$class->$method();
      }
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
  function GetReviveTarget(User $user) {
    //蘇生データ取得
    $event  = DB::$ROOM->IsEvent('full_revive') ? 100 : (DB::$ROOM->IsEvent('no_revive') ? 0 : null);
    $class  = $this->GetClass($method = 'GetReviveRate');
    $revive = isset($event) ? $event : $class->$method(); //蘇生率
    if ($this->IsBoostRevive()) $revive = min(100, ceil($revive * 1.3));

    $missfire = isset($event) ? 0 :
      (isset($this->missfire_rate) ? $this->missfire_rate : floor($revive / 5)); //誤爆率
    if (DB::$ROOM->IsEvent('missfire_revive')) $missfire *= 2;
    if ($missfire > $revive) $missfire = $revive;

    $rand = mt_rand(1, 100); //蘇生判定用乱数
    //$rand = 5; //mt_rand(1, 10); //テスト用
    //Text::p("{$revive} ({$missfire})", "Info: {$this->GetUname()} => {$user->uname}");
    //Text::p($rand, 'Rate: ' . $this->GetUname());

    if ($rand > $revive) return null; //蘇生失敗
    if ($rand <= $missfire) { //誤爆蘇生
      $stack = array();
      //現時点の身代わり君と蘇生能力者が選んだ人以外の死者と憑依者を検出
      foreach (DB::$USER->rows as $target) {
	if ($target->IsDummyBoy() || $target->revive_flag || $user->IsSame($target) ||
	    $target->IsReviveLimited()) continue;
	if ($target->dead_flag || ! DB::$USER->IsVirtualLive($target->id, true)) {
	  $stack[] = $target->id;
	}
      }
      //Text::p($stack, 'Target/Missfire');
      //候補がいる時だけ入れ替える
      if (count($stack) > 0) $user = DB::$USER->ByID(Lottery::Get($stack));
    }
    //$target = DB::$USER->ByID(24); //テスト用
    //Text::p($user->uname, 'ReviveUser');
    return $user->IsReviveLimited() ? null : $user; //蘇生失敗判定
  }

  //蘇生率取得
  function GetReviveRate() { return $this->revive_rate; }

  //蘇生率強化判定
  protected function IsBoostRevive() {
    $data = 'boost_revive';
    if (! is_null($flag = $this->GetStack($data))) return $flag;
    $flag = false;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLiveRole('revive_brownie', true)) {
	$flag = true;
	break;
      }
    }
    $this->SetStack($flag, $data);
    return $flag;
  }

  //蘇生実行
  function ReviveUser(User $user) {
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
  function ReviveAction() {}
}
