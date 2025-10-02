<?php
//-- 個別投票クラス (Role 拡張) --//
//-- ◆文字化け抑制◆ --//
class RoleVote {
  //-- 共通 --//
  //後追い
  public static function Followed() {
    RoleLoader::Load('lovers')->Followed();
    RoleLoader::Load('medium')->InsertMediumResult();
  }

  //-- 処刑投票 --//
  //投票数補正 (メイン役職)
  public static function VoteDoMain() {
    self::FilterUser(DB::$SELF, 'vote_do_main', 'FilterVoteDo');
  }

  //投票数補正 (サブ役職/仮想ユーザ))
  public static function VoteDoSub() {
    if (DB::$ROOM->IsEvent('no_authority')) { //蜃気楼ならスキップ
      return;
    }
    self::FilterUser(DB::$SELF->GetVirtual(), 'vote_do_sub', 'FilterVoteDo');
  }

  //得票数補正 (メイン役職/実ユーザ)
  public static function VotePollMain(User $user) {
    self::FilterUser($user, 'vote_poll_main', 'FilterVotePoll');
  }

  //得票数補正 (サブ役職/仮想ユーザ)
  public static function VotePollSub(User $user) {
    if (DB::$ROOM->IsEvent('no_authority')) { //蜃気楼ならスキップ
      return;
    }
    self::FilterUser($user, 'vote_poll_sub', 'FilterVotePoll');
  }

  //処刑投票魔法
  public static function VoteKillWizard($user) {
    foreach (RoleLoader::LoadUser($user, 'vote_kill_wizard') as $filter) {
      $filter->SetWizard();
      //Text::p($user->virtual_role, "◆Wizard [{$user->uname}]");
    }
  }

  //処刑投票能力セット (メイン役職/実ユーザ)
  public static function VoteKillMain(User $user, User $target) {
    foreach (RoleLoader::LoadUser($user, 'vote_kill_main', false, true) as $filter) {
      $filter->SetStackVoteKill($target->uname);
    }
  }

  //処刑投票能力セット (サブ役職/仮想ユーザ)
  public static function VoteKillSub(User $user, User $target) {
    foreach (RoleLoader::LoadUser($user, 'vote_kill_sub', false) as $filter) {
      $filter->SetStackVoteKill($target->uname);
    }
  }

  //処刑投票補正
  public static function VoteKillCorrect() {
    //RoleManager::Stack()->p(VoteDayElement::COUNT, '◆VoteCount');
    self::Filter('vote_kill_correct', __FUNCTION__);
  }

  //処刑者決定
  public static function DecideVoteKill() {
    self::Filter('decide_vote_kill', __FUNCTION__);
  }

  //毒鑑定情報収集
  public static function SetDetox() {
    self::Filter('distinguish_poison', __FUNCTION__);
  }

  //解毒判定
  public static function Detox() {
    $role  = 'alchemy_pharmacist'; //錬金術師
    $user  = self::GetVoteKill();
    $actor = $user->GetVirtual(); //投票データは仮想ユーザ
    $actor->detox = false;
    $actor->$role = false;
    RoleLoader::SetActor($actor);

    if ($user->IsRole('dummy_poison')) { //夢毒者は対象外
      return false;
    }
    self::Filter('detox', __FUNCTION__);
    return RoleLoader::GetActor()->detox;
  }

  //処刑毒死候補者選出
  public static function GetVoteKillPoisonTarget() {
    //毒の対象オプションをチェックして初期候補者リストを作成後に対象者を取得
    if (GameConfig::POISON_ONLY_VOTER) { //投票した人限定
      $uname = self::GetVoteKill()->uname;
      $stack = RoleManager::Stack()->GetKeyList(VoteDayElement::TARGET_LIST, $uname);
    } else {
      $stack = RoleManager::Stack()->Get(VoteDayElement::LIVE_LIST);
    }
    //Text::p($stack, '◆BaseTarget [poison]');

    $role = 'alchemy_pharmacist'; //錬金術師
    if (RoleLoader::GetActor()->$role || DB::$ROOM->IsEvent($role)) { //$actor は Detox() でセット
      $user = new User($role);
    } else {
      $user = self::GetVoteKill();
    }
    $method = __FUNCTION__;
    return RoleLoader::LoadMain($user)->$method($stack);
  }

  //抗毒判定
  public static function ResistVoteKillPoison(User $user) {
    $method = __FUNCTION__;
    foreach (RoleLoader::LoadUser($user, 'resist_vote_kill_poison') as $filter) {
      if ($filter->$method()) {
	return true;
      }
    }
    return false;
  }

  //連毒
  public static function ChainPoison(User $user) {
    self::FilterUser($user, 'chain_poison', __FUNCTION__);
  }

  //処刑者カウンター
  public static function VoteKillCounter() {
    $method = __FUNCTION__;
    $user   = self::GetVoteKill();
    $stack  = RoleManager::Stack()->GetKeyList(VoteDayElement::TARGET_LIST, $user->uname); //投票者
    foreach (RoleLoader::LoadUser($user, 'vote_kill_counter') as $filter) {
      $filter->$method($stack);
    }
  }

  //処刑投票能力
  public static function VoteKillAction() {
    self::GetVoteKill()->stolen_flag = false;
    self::Filter('vote_kill_action', __FUNCTION__);
  }

  //霊能
  public static function Necromancer() {
    //-- 初期化 --//
    $user        = self::GetVoteKill();
    $stolen_flag = DB::$ROOM->IsEvent('corpse_courier_mad') || $user->stolen_flag; //火車の妨害判定
    $role_flag   = new stdClass();
    $wizard_flag = new stdClass();
    foreach (RoleFilterData::$necromancer as $role) { //対象役職を初期化
      $role_flag->$role   = false;
      $wizard_flag->$role = false;
    }
    foreach (DB::$USER->GetRole() as $role => $list) {
      if (RoleDataManager::IsMain($role)) {
	$role_flag->$role = true;
      }
    }
    RoleManager::Stack()->Set('necromancer_wizard', $wizard_flag);
    //Text::p($role_flag, '◆ROLE_FLAG');

    //-- 霊能魔法 --//
    foreach (RoleFilterData::$necromancer_wizard as $role) {
      if (isset($role_flag->$role)) { //SetWizard() が $actor 更新あり
	RoleLoader::LoadMain(new User($role))->NecromancerWizard($user, $stolen_flag);
      }
    }

    //-- 霊能 --//
    $name = $user->GetName();
    foreach (RoleFilterData::$necromancer as $role) {
      if ($role_flag->$role || $wizard_flag->$role) {
	$filter = RoleLoader::Load($role);
	$result = $filter->Necromancer($user, $stolen_flag);
	if (is_null($result)) {
	  continue;
	}

	if ($role_flag->$role) {
	  DB::$ROOM->StoreAbility($filter->result, $result, $name);
	}

	if ($wizard_flag->$role) {
	  $wizard_result = RoleManager::Stack()->Get('necromancer_wizard_result');
	  DB::$ROOM->StoreAbility($wizard_result, $result, $name);
	}
      }
    }

    //-- スタック消去 --//
    RoleManager::Stack()->Clear('necromancer_wizard');
    RoleManager::Stack()->Clear('necromancer_wizard_result');
  }

  //処刑得票カウンター
  public static function VotePollReaction() {
    self::Filter('vote_poll_reaction', __FUNCTION__);
  }

  //青天の霹靂判定
  public static function SetThunderbolt() {
    RoleManager::Stack()->Init('thunderbolt');
    if (DB::$ROOM->IsEvent('thunderbolt')) {
      RoleLoader::Load('thunder_brownie')->SetThunderboltTarget();
    } else {
      self::Filter('thunderbolt', __FUNCTION__);
    }
  }

  //ショック死判定 (青天の霹靂)
  public static function SuddenDeathThunderbolt() {
    RoleLoader::Load('thunder_brownie')->SuddenDeath();
  }

  //ショック死判定 (サブ役職)
  public static function SuddenDeathSub() {
    if (DB::$ROOM->IsEvent('no_sudden_death')) { //凪ならスキップ
      return;
    }

    foreach (RoleLoader::LoadType('sudden_death_sub') as $filter) {
      $filter->SuddenDeath();
    }
  }

  //ショック死判定 (メイン役職)
  public static function SuddenDeathMain() {
    foreach (RoleLoader::LoadType('sudden_death_main') as $filter) {
      $filter->SuddenDeath();
    }
  }

  //ショック死判定 (天狗陣営)
  public static function SuddenDeathTengu(User $user) {
    if ($user->IsMainCamp(Camp::TENGU)) {
      RoleLoader::LoadMain($user)->SuddenDeath();
    }
  }

  //治療判定
  public static function Cure() {
    self::Filter('cure', __FUNCTION__);
  }

  //処刑道連れ
  public static function VoteKillFollowed() {
    self::Filter('vote_kill_followed', __FUNCTION__);
  }

  //処刑後得票カウンター
  public static function VoteKillReaction() {
    self::Filter('vote_kill_reaction', __FUNCTION__);
  }

  //処刑キャンセル
  public static function VoteKillCancel() {
    self::FilterUser(self::GetVoteKill(), 'vote_kill_cancel', __FUNCTION__);
  }

  //-- 夜投票 --//
  //夜投票共通
  public static function FilterNight(array $list, $method, $dead_type = null, $target_type = null) {
    //Text::p($list, "◆[{$method}]/{$dead_type}/{$target_type}");
    foreach ($list as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      switch ($dead_type) {
      case 'none':
	$skip = false;
	break;

      case 'inactive':
	$skip = $user->IsInactive(); //行動不能判定
	break;

      default:
	$skip = $user->IsDead(true); //直前に死んでいたら無効
	break;
      }
      if (true === $skip) {
	continue;
      }

      switch ($target_type) {
      case 'direct':
	$target = $target_id;
	break;

      case 'multi':
	$target =Text::Parse($target_id);
	break;

      case 'step':
	$target = DB::$USER->ByID(Text::CutPop($target_id, ' '));
	break;

      default:
	$target = DB::$USER->ByID($target_id);
	break;
      }
      RoleLoader::LoadMain($user)->$method($target);
    }
  }

  //夜投票 (足音型)
  public static function FilterNightStep(array $list, $method) {
    self::FilterNight($list, $method, null, 'step');
  }

  //夜投票 (死亡判定無し)
  public static function FilterNightSet(array $list, $method) {
    self::FilterNight($list, $method, 'none');
  }

  //-- 共通処理 --//
  //共通フィルタ
  private static function Filter($type, $method) {
    foreach (RoleLoader::LoadFilter($type) as $filter) {
      //Text::p($filter, "◆{$type}");
      $filter->$method();
    }
  }

  //共通フィルタ (ユーザ指定)
  private static function FilterUser(User $user, $type, $method) {
    foreach (RoleLoader::LoadUser($user, $type) as $filter) {
      //Text::p($filter, "◆{$type}");
      $filter->$method();
    }
  }

  //処刑者取得
  private static function GetVoteKill() {
    return RoleManager::Stack()->Get(VoteDayElement::VOTED_USER);
  }
}
