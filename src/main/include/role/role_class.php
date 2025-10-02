<?php
//-- 役職ローダー --//
class RoleLoader extends LoadManager {
  const PATH = '%s/role/%s.php';
  const CLASS_PREFIX = 'Role_';
  const MAIN = 'main_role';
  protected static $file  = []; //ロード済みファイル
  protected static $class = []; //ロード済みクラス
  private static $actor; //対象ユーザ

  //個別クラスロード (Mixin 用)
  public static function LoadMix($name) {
    if (false === self::LoadFile($name)) {
      return null;
    }
    $class = self::CLASS_PREFIX . $name;
    return new $class();
  }

  //-- フィルタ関連 --//
  //フィルタロード
  public static function LoadType($type, $shift = false, $virtual = false) {
    $stack = [];
    $virtual |= ($type == self::MAIN);
    foreach (self::GetList($type) as $role) {
      if ($virtual ? self::$actor->IsRole(true, $role) : self::$actor->IsRole($role)) {
	$stack[] = $role;
	self::Load($role);
      }
    }
    $filter = self::GetFilter($stack);
    return (true === $shift) ? array_shift($filter) : $filter;
  }

  //個別クラス取得 ($actor 参照型)
  public static function LoadUser(User $user, $type, $shift = false, $virtual = false) {
    self::SetActor($user);
    return self::LoadType($type, $shift, $virtual);
  }

  //メイン役職クラスロード
  public static function LoadMain(User $user) {
    return self::LoadUser($user, self::MAIN, true);
  }

  //フィルタ用クラスロード
  public static function LoadFilter($type) {
    return self::GetFilter(self::GetList($type));
  }

  //-- ユーザ関連 --//
  //ユーザ取得
  public static function GetActor() {
    return self::$actor;
  }

  //ユーザセット
  public static function SetActor(User $user) {
    self::$actor = $user;
  }

  //-- private メソッド --//
  //役職リスト取得
  private static function GetList($type) {
    return $type == self::MAIN ? [self::$actor->GetMainRole(true)] : RoleFilterData::$$type;
  }

  //役職リストに応じたクラスリスト取得
  private static function GetFilter(array $list) {
    $stack = [];
    foreach ($list as $name) { //順番依存があるので配列関数を使わないで処理する
      if (self::IsLoadedClass($name)) {
	$stack[] = static::$class[$name];
      }
    }
    return $stack;
  }
}

//-- 役職マネージャ --//
class RoleManager {
  //スタック取得
  public static function Stack() {
    static $stack;

    if (is_null($stack)) {
      $stack = new Stack();
    }
    return $stack;
  }

  //投票データ取得
  public static function GetVoteData() {
    return self::Stack()->Get('vote_data');
  }

  //投票データセット
  public static function SetVoteData(array $list) {
    return self::Stack()->Set('vote_data', $list);
  }
}

//-- 役職の基底クラス --//
abstract class Role {
  public $role;

  //-- システム関数 --//
  public function __construct() {
    $this->role = Text::CutPop(get_class($this), RoleLoader::CLASS_PREFIX);
    //Text::p(get_class_vars(get_class($this)), "◆{$this->role}");
  }

  //Mixin 呼び出し
  public function __call($name, $args) {
    if (false === isset($this->mix_in)) {
      return $this->ReturnError($name, 'Mixin');
    }

    $filter = $this->GetMethod($name);
    if (is_null($filter)) {
      return $this->ReturnError($name, 'Method');
    }

    return call_user_func_array([$filter, $name], $args);
  }

  //プロパティ呼び出し
  public function __get($name) {
    switch ($name) {
    case 'action':
    case 'not_action':
    case 'add_action':
    case 'submit':
    case 'not_submit':
    case 'add_submit':
      $this->$name = null;
      return;

    case 'mix_in':
    case 'mix_in_list':
    case 'method_list':
      $this->$name = [];
      return;

    default:
      $this->ReturnError($name, 'Property');
      return null;
    }
  }

  //Mixin 呼び出しエラー
  final protected function ReturnError($name, $type) {
    switch ($type) {
    case 'Property':
      $target = sprintf('$%s', $name);
      break;

    default:
      $target = sprintf('%s()', $name);
      break;
    }
    $format = '%sError: %s not found: %s: %s';
    Text::p(sprintf($format, Message::SYMBOL, $type, get_class($this), $target));
    return false;
  }

  //-- Mixin 関連 --//
  //Mixin ロード
  final protected function LoadMix($name) {
    $filter = RoleLoader::LoadMix($name);
    $filter->role = $this->role;
    if (isset($this->display_role)) {
      $filter->display_role = $this->display_role;
    }
    return $filter;
  }

  //Mixin 登録
  final public function AddMix($name) {
    if (false === isset($this->mix_in_list[$name])) {
      //Text::p($name, "◆{$this->role}");
      $this->mix_in_list[$name] = $this->LoadMix($name);
    }
    return $this;
  }

  //Mixin クラス取得
  final protected function GetMix($name) {
    return ArrayFilter::Get($this->mix_in_list, $name);
  }

  //Mixin 関数保持クラス取得
  final protected function GetMethod($name) {
    if (isset($this->method_list[$name])) {
      //Text::t($name . '/' . $this->method_list[$name], "◆Method/Cache [{$this->role}]");
      return $this->GetMix($this->method_list[$name]);
    }
    //Text::t($name, "◆Method/Search [{$this->role}]");

    foreach ($this->mix_in as $role) {
      $filter = $this->AddMix($role)->GetMix($role);
      if (method_exists($filter, $name)) {
	$this->method_list[$name] = $role;
	return $filter;
      }
    }
    return null;
  }

  //Mixin 親クラス取得
  final protected function GetParent($method) {
    $class = RoleLoader::CLASS_PREFIX . $this->role;
    if ($class == get_class($this)) {
      return $this;
    }
    return method_exists($class, $method) ? RoleLoader::Load($this->role) : $this;
  }

  //Mixin 親クラス関数実行
  final protected function CallParent($method, $arg = null) {
    //Text::p($method, "◆CallParent [{$this->role}]");
    $class = $this->GetParent($method);
    return is_null($arg) ? $class->$method() : $class->$method($arg);
  }

  //投票用 Mixin 存在判定
  final protected function ExistsVoteMix() {
    if (false === isset($this->mix_in) || false === isset($this->mix_in['vote'])) {
      return false;
    }
    $this->AddMix($this->mix_in['vote']);
    return true;
  }

  //投票用 Mixin 取得
  final protected function GetVoteMix() {
    return $this->GetMix($this->mix_in['vote']);
  }

  //投票用 Mixin 実行
  final protected function CallVoteMix($name) {
    return $this->GetVoteMix()->$name();
  }

  //-- 汎用関数 --//
  //ユーザ取得
  final protected function GetActor() {
    return RoleLoader::GetActor();
  }

  //ユーザ ID 取得
  final protected function GetID() {
    return $this->GetActor()->id;
  }

  //ユーザ名取得
  final protected function GetUname($uname = null) {
    return is_null($uname) ? $this->GetActor()->uname : $uname;
  }

  //データ取得
  final protected function GetStack($name = null, $fill = false) {
    $stack = RoleManager::Stack()->Get(is_null($name) ? $this->role : $name);
    return isset($stack) ? $stack : ArrayFilter::Fill($fill);
  }

  //データ取得 (key ベース)
  final protected function GetStackKey($name = null, $key = null) {
    return ArrayFilter::GetKeyList($this->GetStack($name), $key);
  }

  //データセット
  final protected function SetStack($data, $role = null) {
    RoleManager::Stack()->Set(is_null($role) ? $this->role : $role, $data);
  }

  //データ初期化
  final protected function InitStack($name = null) {
    $data  = is_null($name) ? $this->role : $name;
    $stack = RoleManager::Stack()->Get($data);
    if (false === isset($stack)) {
      RoleManager::Stack()->Init($data);
    }
  }

  //データ追加
  final protected function AddStack($data, $role = null, $id = null) {
    if (is_null($id)) {
      $id = $this->GetID();
    }
    $name  = is_null($role) ? $this->role : $role;
    $stack = RoleManager::Stack()->Get($name);
    $stack[$id] = $data;
    RoleManager::Stack()->Set($name, $stack);
  }

  //データ追加 (Uname 用)
  final protected function AddStackName($data, $role = null, $uname = null) {
    $name  = is_null($role) ? $this->role : $role;
    $stack = RoleManager::Stack()->Get($name);
    $stack[$this->GetUname($uname)] = $data;
    RoleManager::Stack()->Set($name, $stack);
  }

  //データ存在判定
  final protected function InStack($data, $name = null) {
    return in_array($data, $this->GetStack($name));
  }

  //同一ユーザ判定
  final protected function IsActor(User $user) {
    return $this->GetActor()->IsSame($user);
  }

  //能力発動判定
  final protected function IsActorActive() {
    return $this->GetActor()->IsActive();
  }

  //発動日判定
  final protected function IsDoom() {
    return $this->GetActor()->GetDoomDate($this->role) == DB::$ROOM->date;
  }

  //-- 投票能力判定 --//
  //投票能力判定
  final public function IsVote() {
    if ($this->ExistsVoteMix()) {
      return $this->CallVoteMix(__FUNCTION__);
    }

    return false === is_null($this->action) && $this->IsVoteDate() && $this->IsAddVote();
  }

  //投票可能日判定
  final protected function IsVoteDate() {
    switch ($this->GetActionDate()) {
    case RoleActionDate::FIRST:
      return DB::$ROOM->IsDate(1);

    case RoleActionDate::AFTER:
      return DB::$ROOM->date > 1;

    default:
      return true;
    }
  }

  //投票可能日タイプ取得
  protected function GetActionDate() {
    return null;
  }

  //投票能力追加判定
  protected function IsAddVote() {
    return true;
  }

  //-- 役職情報表示 --//
  //役職情報表示
  final public function OutputAbility() {
    if ($this->IgnoreAbility()) {
      return;
    }

    $this->OutputImage();
    $this->OutputPartner();
    $this->OutputResult();
    if (DB::$ROOM->IsNight() && $this->IsVote()) {
      $this->OutputAction();
    }
  }

  //役職情報表示スキップ判定
  protected function IgnoreAbility() {
    return false;
  }

  //役職画像表示
  final protected function OutputImage() {
    if ($this->IgnoreImage()) {
      return;
    }

    ImageManager::Role()->Output($this->GetImage());
  }

  //役職画像表示スキップ判定
  protected function IgnoreImage() {
    return false;
  }

  //役職画像表示対象取得
  protected function GetImage() {
    return isset($this->display_role) ? $this->display_role : $this->role;
  }

  //仲間情報表示
  final protected function OutputPartner() {
    if ($this->IgnorePartner()) {
      return;
    }

    foreach ($this->GetPartner() as $type => $list) {
      $this->OutputPartnerByType($list, $type);
    }
    $this->OutputAddPartner();
  }

  //仲間情報表示スキップ判定
  protected function IgnorePartner() {
    return false;
  }

  //仲間リスト取得
  protected function GetPartner() {
    return [];
  }

  //個別仲間リスト表示
  protected function OutputPartnerByType(array $list, $type) {
    RoleHTML::OutputPartner($list, $type);
  }

  //追加仲間表示
  protected function OutputAddPartner() {}

  //能力結果表示
  final protected function OutputResult() {
    if ($this->IgnoreResult()) {
      return;
    }

    if (isset($this->result)) {
      RoleHTML::OutputResult($this->result);
    }
    $this->OutputAddResult();
  }

  //能力結果表示スキップ判定
  protected function IgnoreResult() {
    return false;
  }

  //追加結果表示
  protected function OutputAddResult() {}

  //投票能力表示
  public function OutputAction() {
    if ($this->ExistsVoteMix()) {
      return $this->CallVoteMix(__FUNCTION__);
    }
  }

  //-- 発言処理 --//
  //閲覧者取得
  final protected function GetViewer() {
    return $this->GetStack('viewer');
  }

  //閲覧者情報取得
  final protected function GetTalkFlag($data) {
    return $this->GetStack('builder')->flag->$data;
  }

  //-- 処刑投票処理 --//
  //実ユーザ判定
  final protected function IsRealActor() {
    return DB::$USER->ByRealUname($this->GetUname())->IsRole(true, $this->role);
  }

  //生存仲間判定
  final protected function IsLivePartner() {
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      if (DB::$USER->ByID($id)->IsLive(true)) {
	return true;
      }
    }
    return false;
  }

  //-- 処刑集計処理 --//
  //処刑投票情報収集
  final public function SetStackVoteKill($uname) {
    switch ($this->GetStackVoteKillType()) {
    case RoleStackVoteKill::ACTOR:
      $this->SetStack($this->GetUname());
      break;

    case RoleStackVoteKill::TARGET:
      $this->SetStack($uname);
      break;

    case RoleStackVoteKill::ADD:
      $this->AddStackName($uname);
      break;

    case RoleStackVoteKill::INIT:
      $this->InitStack();
      $this->AddStackName($uname);
      break;

    case RoleStackVoteKill::ETC:
      $this->SetStackVoteKillEtc($uname);
      break;
    }
  }

  //処刑投票情報収集タイプ取得
  protected function GetStackVoteKillType() {
    return null;
  }

  //処刑投票情報収集 (特殊)
  protected function SetStackVoteKillEtc($uname) {}

  //処刑者ユーザ名取得
  final protected function GetVoteKill() {
    return $this->GetStack(VoteDayElement::VOTE_KILL);
  }

  //処刑者決定済判定
  final protected function DetermineVoteKill() {
    return false === is_null($this->GetVoteKill());
  }

  //処刑者判定
  final protected function IsVoteKill($uname = null) {
    return $this->GetVoteKill() == $this->GetUname($uname);
  }

  //処刑得票ユーザー名リスト取得
  final protected function GetVotePollList($uname = null) {
    return $this->GetStackKey(VoteDayElement::TARGET_LIST, $this->GetUname($uname));
  }

  //処刑投票先ユーザ名取得
  final protected function GetVoteKillUname($uname = null) {
    $stack = $this->GetStack(VoteDayElement::TARGET_LIST);
    return $stack[$this->GetUname($uname)];
  }

  //処刑投票先ユーザ取得
  final protected function GetVoteKillUser($uname = null) {
    return DB::$USER->ByRealUname($this->GetVoteKillUname($uname));
  }

  //-- 夜投票データ表示 --//
  //夜投票データセット
  final public function SetVoteNight() {
    if ($this->ExistsVoteMix()) {
      return $this->CallVoteMix(__FUNCTION__);
    }

    if (false === $this->IsVote()) {
      VoteHTML::OutputResult($this->GetDisabledVoteNightMessage());
    }

    foreach (['', 'not_', 'add_'] as $header) {
      foreach (['action', 'submit'] as $data) {
	$this->SetStack($this->{$header . $data}, $header . $data);
      }
    }
    if (isset($this->not_action) && $this->DisableNotAction()) {
      $this->SetStack(null, 'not_action');
    }
    if (isset($this->add_action) && $this->DisableAddAction()) {
      $this->SetStack(null, 'add_action');
    }
    $this->SetVoteNightFilter();
  }

  //夜投票無効メッセージ取得
  final protected function GetDisabledVoteNightMessage() {
    if (is_null($this->action)) {
      return VoteRoleMessage::NO_ACTION;
    } elseif (false === $this->IsVoteDate()) {
      switch ($this->GetActionDate()) {
      case RoleActionDate::FIRST:
	return VoteRoleMessage::POSSIBLE_ONLY_FIRST_DAY;

      case RoleActionDate::AFTER:
	return VoteRoleMessage::IMPOSSIBLE_FIRST_DAY;

      default: //ここに来たらロジックエラー
	return VoteHTML::OutputError(VoteMessage::INVALID_COMMAND);
      }
    } elseif (false === $this->IsAddVote()) {
      return $this->GetDisabledAddVoteNightMessage();
    } else { //ここに来たらロジックエラー
      return VoteHTML::OutputError(VoteMessage::INVALID_COMMAND);
    }
  }

  //夜投票無効メッセージ取得 (追加判定)
  protected function GetDisabledAddVoteNightMessage() {
    return null;
  }

  //キャンセル投票無効判定
  protected function DisableNotAction() {
    return false;
  }

  //追加投票無効判定
  protected function DisableAddAction() {
    return false;
  }

  //夜投票データセット追加処理
  protected function SetVoteNightFilter() {}

  //-- 夜投票画面表示 --//
  //夜投票対象ユーザ取得
  final public function GetVoteNightTargetUser() {
    return $this->GetVoteNightTargetUserFilter(DB::$USER->Get());
  }

  //夜投票対象ユーザフィルタ
  protected function GetVoteNightTargetUserFilter(array $list) {
    return $list;
  }

  //夜投票アイコンパス取得
  final public function GetVoteNightIconPath(User $user, $live) {
    if ($this->ExistsVoteMix()) {
      $name = __FUNCTION__;
      return $this->GetVoteMix()->$name($user, $live);
    }

    if (true === $live || $this->FixLiveVoteNightIconPath()) {
      $path = $this->GetPartnerVoteNightIconPath($user);
      return is_null($path) ? Icon::GetFile($user->icon_filename) : $path;
    } else {
      return Icon::GetDead();
    }
  }

  //夜投票アイコンパス生存固定判定
  protected function FixLiveVoteNightIconPath() {
    return false;
  }

  //夜投票アイコンパス取得 (仲間)
  protected function GetPartnerVoteNightIconPath(User $user) {
    return null;
  }

  //夜投票チェックボックス取得
  final public function GetVoteNightCheckbox(User $user, $id, $live) {
    if ($this->ExistsVoteMix()) {
      $name = __FUNCTION__;
      return $this->GetVoteMix()->$name($user, $id, $live);
    }

    if ($this->IsVoteNightCheckbox($user, $live)) {
      $type    = $this->GetVoteNightCheckboxType();
      $checked = $this->CheckedVoteNightCheckbox($user);
      return RoleHTML::GenerateVoteNightCheckbox($type, $id, $checked);
    } else {
      return '';
    }
  }

  //夜投票対象判定 (生死 / 本人 / 身代わり君 / 追加判定)
  final protected function IsVoteNightCheckbox(User $user, $live) {
    $is_live = $this->IsVoteNightCheckboxLive($live);
    $self    = $this->DisableVoteNightCheckboxSelf() && $this->IsActor($user);
    $dummy   = $this->DisableVoteNightCheckboxDummyBoy() && $user->IsDummyBoy();
    $filter  = $this->IsVoteNightCheckboxFilter($user);
    return true === $is_live && false === $self && false === $dummy && true === $filter;
  }

  //夜投票対象判定 (生死)
  protected function IsVoteNightCheckboxLive($live) {
    return $live;
  }

  //夜投票対象判定 (本人除外)
  protected function DisableVoteNightCheckboxSelf() {
    return true;
  }

  //夜投票対象判定 (身代わり君除外)
  protected function DisableVoteNightCheckboxDummyBoy() {
    return false;
  }

  //夜投票対象判定 (追加)
  protected function IsVoteNightCheckboxFilter(User $user) {
    return true;
  }

  //夜投票チェックボックス種別取得
  protected function GetVoteNightCheckboxType() {
    return OptionFormType::RADIO;
  }

  //夜投票対象自動チェック判定
  protected function CheckedVoteNightCheckbox(User $user) {
    return false;
  }

  //-- 投票処理 (夜) --//
  //夜投票完了判定
  final public function CompletedVoteNight(array $list) {
    if ($this->ExistsVoteMix()) {
      $name = __FUNCTION__;
      return $this->GetVoteMix()->$name($list);
    }

    return false === $this->IsVote() || $this->IgnoreCompletedVoteNight() ||
      $this->ExistsAction($list);
  }

  //夜投票完了判定スキップ判定
  protected function IgnoreCompletedVoteNight() {
    return false;
  }

  //夜投票コマンド存在判定
  protected function ExistsAction(array $list) {
    return $this->ExistsSelfAction($list);
  }

  //個別夜投票コマンド存在判定
  final protected function ExistsSelfAction(array $list) {
    if ($this->DisableNotAction()) {
      unset($list[$this->not_action]);
    }
    if ($this->DisableAddAction()) {
      unset($list[$this->add_action]);
    }
    $id = $this->GetID();
    return ArrayFilter::IsAssocKey($list, $this->action, $id) ||
      ArrayFilter::IsIncludeKey($list, $this->not_action, $id) ||
      ArrayFilter::IsIncludeKey($list, $this->add_action, $id);
  }

  //夜投票対象者データセット
  final public function SetVoteNightTarget() {
    if ($this->ExistsVoteMix()) {
      return $this->CallVoteMix(__FUNCTION__);
    }

    $this->SetStack(RQ::Get()->situation, 'message');
    try {
      $stack = RQ::Get()->target_no;
      if (is_array($stack)) {
	$this->ValidateVoteNightTargetList($stack);
	$this->SetVoteNightTargetList($stack);
	$this->CallParent('SetVoteNightTargetListAction');
      } else {
	$user = DB::$USER->ByID($stack);
	$live = DB::$USER->IsVirtualLive($user->id); //生死判定は仮想を使う
	$this->ValidateVoteNightTarget($user, $live);
	
	//憑依者がすでに死んでいたら元の投票先を見る (死者投票型対応)
	$real = DB::$USER->ByReal($user->id);
	$target = $real->IsLive() ? $real : $user;
	$this->SetStack($target->id, RequestDataVote::TARGET);
	$this->SetStack($user->handle_name, 'target_handle');
      }
    } catch (Exception $e) {
      VoteHTML::OutputResult(Text::Join(VoteRoleMessage::INVALID_TARGET, $e->getMessage()));
    }
  }

  //複合投票型夜投票無効判定
  protected function ValidateVoteNightTargetList(array $list) {
    $count = $this->GetVoteNightNeedCount();
    if (count($list) != $count) {
      $str = sprintf(VoteRoleMessage::INVALID_TARGET_COUNT, $count);
      throw new UnexpectedValueException($str);
    }
  }

  //夜投票所要人数取得
  protected function GetVoteNightNeedCount() {
    return 2;
  }

  //複合投票型夜投票対象者セット
  public function SetVoteNightTargetList(array $list) {
    return null;
  }

  //複合型夜投票対象者データセット追加処理
  public function SetVoteNightTargetListAction() {}

  //夜投票無効判定
  final public function ValidateVoteNightTarget(User $user, $live) {
    if ($this->IsInvalidVoteNightTargetLive($live)) {
      if (true === $live) {
	throw new UnexpectedValueException(VoteRoleMessage::TARGET_ALIVE);
      } else {
	throw new UnexpectedValueException(VoteRoleMessage::TARGET_DEAD);
      }
    } elseif ($this->DisableVoteNightCheckboxSelf() && $this->IsActor($user)) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_MYSELF);
    } elseif ($this->DisableVoteNightCheckboxDummyBoy() && $user->IsDummyBoy()) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_DUMMY_BOY);
    } else {
      $this->ValidateVoteNightTargetFilter($user);
    }
  }

  //夜投票無効生死判定
  protected function IsInvalidVoteNightTargetLive($live) {
    return false === $this->IsVoteNightCheckboxLive($live);
  }

  //夜投票無効追加判定
  protected function ValidateVoteNightTargetFilter(User $user) {}

  //-- 投票集計処理 (夜) --//
  //成功データ追加
  final protected function AddSuccess($target, $data = null, $set_null = false) {
    $name  = is_null($data) ? $this->role : $data;
    $stack = RoleManager::Stack()->Get($name);
    $stack[$target] = (true === $set_null) ? null : true;
    RoleManager::Stack()->Set($name, $stack);
  }

  //投票者取得
  final protected function GetVoter() {
    return $this->GetStack('voter');
  }

  //襲撃人狼取得
  final protected function GetWolfVoter() {
    return $this->GetStack('voted_wolf');
  }

  //人狼襲撃対象者取得
  final protected function GetWolfTarget() {
    return $this->GetStack('wolf_target');
  }

  //-- 勝敗判定 --//
  //勝利判定
  public function Win($winner) {
    return true;
  }

  //生存判定
  final protected function IsActorLive($strict = false) {
    return $this->GetActor()->IsLive($strict);
  }

  //死亡判定
  final protected function IsActorDead($strict = false) {
    return $this->GetActor()->IsDead($strict);
  }
}

//-- 発言処理クラス (Role 拡張) --//
class RoleTalk {
  //location 判定
  public static function GetLocation(User $user, User $real) {
    if (DB::$ROOM->IsEvent('blind_talk_night')) { //天候：風雨
      return TalkLocation::MONOLOGUE;
    }

    if (RoleUser::IsCommon($user)) { //共有者
      return TalkLocation::COMMON;
    } elseif (RoleUser::IsWolf($user)) { //人狼
      //犬神判定
      return $real->IsRole('possessed_mad') ? TalkLocation::MONOLOGUE : TalkLocation::WOLF;
    } elseif ($user->IsRole('whisper_mad')) { //囁き狂人
      //犬神判定
      return $real->IsRole('possessed_mad') ? TalkLocation::MONOLOGUE : TalkLocation::MAD;
    } elseif (RoleUser::IsFox($user)) { //妖狐
      return TalkLocation::FOX;
    } else { //独り言
      return TalkLocation::MONOLOGUE;
    }
  }

  //置換処理
  public static function Convert(&$say) {
    if ($say == '') { //リロード時なら処理スキップ
      return null;
    }

    //文字数・行数チェック
    if (Text::Over($say, GameConfig::LIMIT_SAY) ||
	substr_count($say, Text::LF) >= GameConfig::LIMIT_SAY_LINE) {
      $say = '';
      return false;
    }

    if (GameConfig::REPLACE_TALK) { //発言置換モード
      $say = strtr($say, GameConfig::$replace_talk_list);
    }

    //死者・ゲームプレイ中以外なら以降はスキップ
    if (DB::$SELF->IsDead() || false === DB::$ROOM->IsPlaying()) {
      return null;
    }
    //if (DB::$SELF->IsDead()) return false; //テスト用

    $virtual = DB::$SELF->GetVirtual(); //仮想ユーザを取得
    RoleManager::Stack()->Set('say', $say);
    do { //発言置換処理
      foreach (RoleLoader::LoadUser($virtual, 'say_convert_virtual') as $filter) {
	if ($filter->ConvertSay()) {
	  break 2;
	}
      }

      foreach (RoleLoader::LoadUser(DB::$SELF, 'say_convert') as $filter) {
	if ($filter->ConvertSay()) {
	  break 2;
	}
      }
    } while (false);

    foreach ($virtual->GetPartner('bad_status', true) as $id => $date) { //妖精の処理
      if ($date != DB::$ROOM->date) {
	continue;
      }

      foreach (RoleLoader::LoadUser(DB::$USER->ByID($id), 'say_bad_status') as $filter) {
	$filter->ConvertSay();
      }
    }

    foreach (RoleLoader::LoadUser($virtual, 'say') as $filter) {
      $filter->ConvertSay(); //他のサブ役職の処理
    }
    $say = RoleManager::Stack()->Get('say');
    RoleManager::Stack()->Clear('say');
    return true;
  }

  //発言登録 (エスケープ処理済みの発言を渡すこと)
  public static function Store(TalkStruct $talk, $update = false) {
    //声の大きさを決定
    $voice = RQ::Get()->font_type;
    $say   = $talk->Get(TalkStruct::SENTENCE);
    if (DB::$ROOM->IsPlaying() && DB::$SELF->IsLive()) {
      foreach (RoleLoader::LoadUser(DB::$SELF->GetVirtual(), 'voice') as $filter) {
	$filter->FilterVoice($voice, $say);
      }
    }
    $talk->Set(TalkStruct::SENTENCE,  $say);
    $talk->Set(TalkStruct::FONT_TYPE, $voice);

    if (DB::$ROOM->IsBeforeGame()) {
      RoomTalk::StoreBeforeGame($say, DB::$SELF, $voice);
    } else {
      if (DB::$ROOM->IsPlaying()) {
	$talk->Set(TalkStruct::ROLE_ID, DB::$SELF->role_id);
      }
      DB::$ROOM->Talk($talk);
    }
    //Text::p($talk);

    if (true === $update) {
      RoomDB::UpdateTime();
    }
  }

  //霊界遺言登録能力者の処理
  public static function StoreHeavenLastWords($str) {
    $method = __FUNCTION__;
    foreach (RoleLoader::LoadUser(DB::$SELF, 'heaven_last_words') as $filter) {
      $filter->$method($str);
    }
  }

  //山彦の処理
  public static function EchoSay() {
    if (DB::$SELF->IsRole('echo_brownie')) {
      RoleLoader::LoadMain(DB::$SELF)->EchoSay();
    }
  }
}

//-- Role 用 Talk 構造体 --//
final class RoleTalkStruct extends TalkStruct {
  //コンストラクタ
  public function __construct($sentence) {
    $this->struct = [
      self::SCENE      => DB::$ROOM->scene,
      self::LOCATION   => null,
      self::UNAME      => DB::$SELF->uname,
      self::ROLE_ID    => null,
      self::ACTION     => null,
      self::SENTENCE   => $sentence,
      self::FONT_TYPE  => null,
      self::SPEND_TIME => 0
    ];
  }
}
