<?php
/*
  ◆天狗 (tengu)
  ○仕様
  ・勝利：村人・人狼のうち人数が少ない陣営の勝利
  ・能力結果：所属陣営
  ・ショック死：同陣営得票 + 確率
  ・神通力：天狗倒し
  ・神通力対象：狩人系・暗殺者系・人狼系・子狐系
*/
class Role_tengu extends Role {
  public $mix_in = ['mage', 'chicken'];
  public $action       = VoteAction::TENGU;
  public $result       = RoleAbility::TENGU_CAMP;
  public $action_date  = RoleActionDate::AFTER;

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::TENGU, RoleAbilityMessage::TENGU, $this->action);
  }

  protected function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || RoleUser::IsAvoidLovers($this->GetActor(), true);
  }

  protected function IsSuddenDeath() {
    $flag = false;
    foreach ($this->GetVotedUname() as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      //恋人は常時除く, 鬼は常時対象, 天狗同士は同陣営
      if ($user->IsWinCamp(Camp::OGRE) || $user->IsWinCamp(Camp::TENGU) ||
	  $user->IsWinCamp($this->GetWinCamp())) {
	$flag = true;
	break;
      }
    }
    return $flag && Lottery::Percent($this->GetTenguSuddenDeathRate());
  }

  //勝利陣営判定
  final protected function GetWinCamp($reparse = false) {
    if ($reparse || RoleManager::Stack()->IsEmpty('tengu_camp')) {
      if ($reparse) { //事前に全ユーザの再パースを行う
	foreach (DB::$USER->Get() as $user) $user->StackReparse();
      }

      $stack = [Camp::HUMAN => 0, Camp::WOLF => 0]; //村と狼は初期値を入れておく
      foreach (DB::$USER->Get() as $user) {
	$target = $reparse ? $user->GetReparse() : $user;
	$camp   = $target->GetWinCamp($reparse);
	//Text::p($camp, "◆Camp [tengu/{$user->uname}]");
	ArrayFilter::Add($stack, $camp);
      }
      //Text::p($stack, '◆Camp [tengu]');

      /* 両方 0 => 村 / どちらかが 0 => 0 じゃない方 / 同数 => 村 */
      $human = $stack[Camp::HUMAN];
      $wolf  = $stack[Camp::WOLF];
      if ($human == 0 && $wolf == 0) {
	$camp = Camp::HUMAN;
      } elseif ($human == 0) {
	$camp = Camp::WOLF;
      } elseif ($wolf  == 0) {
	$camp = Camp::HUMAN;
      } else {
	$camp = $human > $wolf ? Camp::WOLF : Camp::HUMAN;
      }
      RoleManager::Stack()->Set('tengu_camp', $camp);
    }
    return RoleManager::Stack()->Get('tengu_camp');
  }

  //ショック死発動率取得
  final protected function GetTenguSuddenDeathRate() {
    if (DB::$ROOM->IsEvent('full_tengu')) {
      return 0;
    } elseif (DB::$ROOM->IsEvent('seal_tengu')) {
      return 100;
    } else {
      return 20;
    }
  }

  protected function GetSuddenDeathType() {
    return 'TENGU_ESCAPE';
  }

  public function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) return false;
    $this->GetMageResult($user);
  }

  protected function GetMageResult(User $user) {
    if ($this->IgnoreTenguTarget($user)) return false;
    if (! Lottery::Percent($this->GetTenguMageRate($user))) return false;
    $this->TenguKill($user);
  }

  //神通力発動対象外判定
  protected function IgnoreTenguTarget(User $user) {
    return ! $user->IsMainGroup(
      CampGroup::GUARD, CampGroup::ASSASSIN, CampGroup::WOLF, CampGroup::CHILD_FOX
    );
  }

  //神通力発動率取得
  final protected function GetTenguMageRate(User $user) {
    if (DB::$ROOM->IsEvent('full_tengu')) {
      return 100;
    } elseif (DB::$ROOM->IsEvent('seal_tengu')) {
      return 0;
    } else {
      $base = $user->IsWinCamp($this->GetWinCamp()) ? 2 : 1; //味方は発動率半減
      return ceil($this->GetTenguMageRateBase() / $base);
    }
  }

  //基礎神通力発動率取得
  protected function GetTenguMageRateBase() {
    return 70;
  }

  //神隠し処理
  protected function TenguKill(User $user) {
    $user->AddRole('tengu_voice');
  }

  //勝利陣営判定
  final public function SetWinCamp() {
    $camp = $this->GetWinCamp(true);
    DB::$ROOM->ResultAbility($this->result, 'result_tengu_camp_' . $camp);
  }

  public function Win($winner) {
    switch ($winner) {
    case Camp::HUMAN:
    case Camp::WOLF:
      return $winner == $this->GetWinCamp();

    default:
      return false;
    }
  }
}
