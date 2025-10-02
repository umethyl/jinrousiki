<?php
/*
  ◆天狗 (tengu)
  ○仕様
  ・勝利：村人・人狼のうち人数が少ない陣営の勝利
  ・ショック死：同陣営得票 + 確率
  ・神通力：天狗倒し
*/
class Role_tengu extends Role {
  public $mix_in = array('mage', 'chicken');
  public $action = 'TENGU_DO';
  public $result = 'TENGU_CAMP_RESULT';
  public $action_date_type = 'after';
  public $sudden_death = 'TENGU_ESCAPE';

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }

  public function OutputAction() {
    RoleHTML::OutputVote('tengu-do', 'tengu_do', $this->action);
  }

  public function Win($winner) {
    switch ($winner) {
    case 'human':
    case 'wolf':
      return $winner == $this->GetWinCamp();

    default:
      return false;
    }
  }

  //勝利陣営判定
  final public function GetWinCamp($reparse = false) {
    if ($reparse || RoleManager::Stack()->IsEmpty('tengu_camp')) {
      if ($reparse) { //事前に全ユーザの再パースを行う
	foreach (DB::$USER->rows as $user) $user->StackReparse();
      }

      $stack = array('human' => 0, 'wolf' => 0); //村と狼は初期値を入れておく
      foreach (DB::$USER->rows as $user) {
	$target = $reparse ? $user->GetReparse() : $user;
	$camp   = $target->GetCamp(true, $reparse);
	//Text::p($camp, "◆Camp [tengu/{$user->uname}]");
	isset($stack[$camp]) ? $stack[$camp]++ : $stack[$camp] = 1;
      }
      //Text::p($stack, '◆Camp [tengu]');

      /* 両方 0 => 村 / どちらかが 0 => 0 じゃない方 / 同数 => 村 */
      $human = $stack['human'];
      $wolf  = $stack['wolf'];
      if ($human == 0 && $wolf == 0) {
	$camp = 'human';
      } elseif ($human == 0) {
	$camp = 'wolf';
      } elseif ($wolf  == 0) {
	$camp = 'human';
      } else {
	$camp = $human > $wolf ? 'wolf' : 'human';
      }
      RoleManager::Stack()->Set('tengu_camp', $camp);
    }
    return RoleManager::Stack()->Get('tengu_camp');
  }

  //勝利陣営判定
  final public function SetWinCamp() {
    $camp = $this->GetWinCamp(true);
    DB::$ROOM->ResultAbility($this->result, 'result_tengu_camp_' . $camp);
  }

  public function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || $this->GetActor()->IsAvoidLovers(true);
  }

  public function IsSuddenDeath() {
    $flag = false;
    foreach ($this->GetVotedUname() as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      //恋人は常時除く・鬼は常時対象・天狗同士は同陣営
      if ($user->IsCamp('ogre', true) || $user->IsCamp('tengu', true) ||
	  $user->IsCamp($this->GetWinCamp(), true)) {
	$flag = true;
	break;
      }
    }
    $rate = DB::$ROOM->IsEvent('full_tengu') ? 0 : (DB::$ROOM->IsEvent('seal_tengu') ? 100 : 20);
    return $flag && Lottery::Percent($rate);
  }

  public function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) return false;
    $this->GetMageResult($user);
  }

  protected function GetMageResult(User $user) {
    if ($this->IgnoreTenguTarget($user)) return false;
    if (! Lottery::Percent($this->GetRate($user))) return false;
    $this->TenguKill($user);
  }

  //神通力発動対象外判定
  protected function IgnoreTenguTarget(User $user) {
    return ! $user->IsMainGroup('guard') && ! $user->IsMainGroup('assassin') &&
      ! $user->IsWolf() && ! $user->IsChildFox();
  }

  //神通力発動率取得
  protected function GetRate(User $user) {
    if (DB::$ROOM->IsEvent('full_tengu')) return 100;
    if (DB::$ROOM->IsEvent('seal_tengu')) return 0;
    return ceil(70 / ($user->IsCamp($this->GetWinCamp(), true) ? 2 : 1));
  }

  //神隠し処理
  protected function TenguKill(User $user) {
    $user->AddRole('tengu_voice');
  }
}
