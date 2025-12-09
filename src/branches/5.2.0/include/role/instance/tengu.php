<?php
/*
  ◆天狗 (tengu)
  ○仕様
  ・勝利：村人・人狼のうち人数が少ない陣営の勝利
  ・能力結果：所属陣営
  ・ショック死：同陣営得票 + 確率
  ・占い：神通力
  ・占い結果：神通力(神隠し)
  ・神通力対象：狩人系・暗殺者系・人狼系・子狐系
  ・神隠し：天狗倒し
*/
class Role_tengu extends Role {
  public $mix_in = ['mage', 'chicken'];
  public $action = VoteAction::TENGU;
  public $result = RoleAbility::TENGU_CAMP;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IgnoreResult() {
    return false === DateBorder::Two();
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::TENGU, RoleAbilityMessage::TENGU, $this->action);
  }

  protected function IgnoreSuddenDeath() {
    return $this->IgnoreSuddenDeathAvoid();
  }

  protected function IsSuddenDeath() {
    $flag = false;
    foreach ($this->GetVotePollList() as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      //恋人は常時除く, 鬼は常時対象, 天狗同士は同陣営
      if ($user->IsWinCamp(Camp::OGRE) || $user->IsWinCamp(Camp::TENGU) ||
	  $user->IsWinCamp($this->GetWinCamp())) {
	$flag = true;
	break;
      }
    }
    return (true === $flag) && Lottery::Percent($this->GetTenguSuddenDeathRate());
  }

  //勝利陣営判定
  final protected function GetWinCamp($reparse = false) {
    if ($reparse || RoleManager::Stack()->IsEmpty('tengu_camp')) {
      if ($reparse) { //事前に全ユーザの再パースを行う
	foreach (DB::$USER->Get() as $user) $user->StackReparse();
      }

      //初期値をセット
      $stack = [Camp::HUMAN => 0, Camp::WOLF => 0, Camp::TENGU => 0];
      foreach (DB::$USER->Get() as $user) {
	$target = $reparse ? $user->GetReparse() : $user;
	$camp   = $target->GetWinCamp($reparse);
	//Text::p($camp, "◆Camp [tengu/{$user->uname}]");
	ArrayFilter::Add($stack, $camp);
      }
      //Text::p($stack, '◆Camp [tengu]');

      /* 天狗不在 => null / 両方 0 => 村 / どちらかが 0 => 0 じゃない方 / 同数 => 村 */
      $human = $stack[Camp::HUMAN];
      $wolf  = $stack[Camp::WOLF];
      $tengu = $stack[Camp::TENGU];
      if ($tengu == 0) {
	$camp = null;
      } elseif ($human == 0 && $wolf == 0) {
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

  //占い(神通力) (妨害 > 呪返し > 神通力発動)
  public function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) {
      return false;
    }
    $this->GetMageResult($user);
  }

  protected function GetMageResult(User $user) {
    if ($this->IgnoreTenguTarget($user)) {
      return false;
    }
    if (false === Lottery::Percent($this->GetTenguMageRate($user))) {
      return false;
    }
    $this->TenguKill($user);
  }

  //神通力発動対象外判定
  protected function IgnoreTenguTarget(User $user) {
    return false === $user->IsMainGroup(
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
    if (null !== $camp) {
      DB::$ROOM->StoreAbility($this->result, 'result_tengu_camp_' . $camp);
    }
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
