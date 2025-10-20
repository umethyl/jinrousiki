<?php
//-- 個別ユーザクラス --//
final class User extends StackManager {
  public $id;
  public $uname;
  public $role;
  public $main_role;
  public $live;
  protected $role_list = [];
  protected $partner   = [];
  protected $updated   = [];

  public function __construct($role = null) {
    if (null === $role) {
      return;
    }

    $this->role = $role;
    $this->Parse();
  }

  //役職情報の展開処理
  public function Parse($role = null) {
    //初期化処理
    if (isset($role)) {
      $this->role = $role;
    }
    $this->partner = [];

    //展開用の正規表現をセット
    $regex_partner = '/([^\[]+)\[([^\]]+)\]/'; //恋人型 (role[id])
    $regex_status  = '/([^-]+)-(.+)/';         //憑依型 (role[date-id])

    //展開処理
    $role_list = [];
    foreach (Text::Parse($this->role) as $role) {
      if (preg_match($regex_partner, $role, $match_partner)) {
	$role_list[] = $match_partner[1];
	if (preg_match($regex_status, $match_partner[2], $match_status)) {
	  $this->partner[$match_partner[1]][$match_status[1]] = $match_status[2];
	} else {
	  $this->partner[$match_partner[1]][] = $match_partner[2];
	}
      } else {
	$role_list[] = $role;
      }
    }

    //代入処理
    $this->role_list = array_unique($role_list);
    $this->main_role = $this->role_list[0] ?? '';
  }

  //役職の再パース処理
  public function Reparse() {
    $this->Parse($this->GetRole());
  }

  //役職再パース + Stack 処理
  public function StackReparse() {
    $role = $this->GetRole();
    if ($this->role == $role) {
      return;
    }

    //Text::p($role, "◆StackReparse [{$this->uname}]");
    $this->reparse = new self($role);
  }

  //player 入れ替え処理
  public function ChangePlayer($id) {
    //未定義、または変更なしならスキップ
    if (false === isset(DB::$USER->player) || false === isset($this->role_id) ||
	$this->role_id == $id) {
      return false;
    }

    $this->role_id = $id;
    $this->Parse(DB::$USER->player->role_list[$id]);
    return true;
  }

  //夜の投票取得
  public function LoadVote($type, $not_type = '') {
    return UserDB::GetVote($this->id, $type, $not_type);
  }

  //仮想ユーザ取得
  public function GetVirtual() {
    return DB::$USER->ByVirtual($this->id);
  }

  //実ユーザ取得
  public function GetReal() {
    return DB::$USER->ByReal($this->id);
  }

  //再パースユーザ取得
  public function GetReparse() {
    return isset($this->reparse) ? $this->reparse : $this;
  }

  //ユーザ ID 取得
  public function GetID($role = null) {
    return isset($role) ? sprintf('%s[%d]', $role, $this->id) : $this->id;
  }

  //HN 取得
  public function GetName() {
    return $this->GetVirtual()->handle_name;
  }

  //役職取得
  public function GetRole() {
    return isset($this->updated['role']) ? $this->updated['role'] : $this->role;
  }

  //メイン役職取得
  public function GetMainRole($virtual = false) {
    if (true === $virtual && isset($this->virtual_role)) {
      return $this->virtual_role;
    } else {
      return $this->main_role;
    }
  }

  //役職リスト取得
  public function GetRoleList() {
    return $this->role_list;
  }

  //サブ役職リスト取得
  public function GetSubRoleList() {
    $stack = $this->role_list;
    array_shift($stack);
    return $stack;
  }

  //役職数取得
  public function GetRoleCount($sub = false) {
    return count($this->role_list) - (true === $sub ? 1 : 0);
  }

  //所属陣営取得
  public function GetCamp($type = 'main_camp', $reparse = false) {
    if ($this->Stack()->IsEmpty($type)) {
      $this->Stack()->Set($type, RoleUser::GetCamp($this, $type, $reparse));
    }
    return $this->Stack()->Get($type);
  }

  //所属陣営取得 (勝利陣営)
  public function GetWinCamp($reparse = false) {
    return $this->GetCamp('win_camp', $reparse);
  }

  //所属陣営取得 (メイン + キャッシュなし)
  public function GetMainCamp($start = false) {
    return RoleDataManager::GetCamp($this->main_role, $start);
  }

  //拡張情報リスト取得
  public function GetPartnerList() {
    return $this->partner;
  }

  //拡張情報取得
  public function GetPartner($type, $fill = false) {
    return ArrayFilter::Cast(ArrayFilter::Get($this->partner, $type), $fill);
  }

  //メイン役職の拡張情報取得
  public function GetMainRoleTarget() {
    return ArrayFilter::Pick($this->GetPartner($this->main_role, true));
  }

  //日数に応じた憑依先の ID 取得
  public function GetPossessedTarget($type, $today) {
    $stack = $this->GetPartner($type);
    if (null === $stack) {
      return false;
    }

    $date_list = array_keys($stack);
    krsort($date_list);
    foreach ($date_list as $date) {
      if ($date <= $today) {
	return $stack[$date];
      }
    }
    return false;
  }

  //死の宣告系の宣告日取得
  public function GetDoomDate($role) {
    return max($this->GetPartner($role));
  }

  //発言数を取得
  public function GetTalkCount($lock = false) {
    if (false === isset($this->talk_count) || true === $lock) {
      $stack = TalkDB::GetUserTalkCount($lock);
      $this->talk_count = (DateBorder::On($stack['date']) ? $stack['talk_count'] : 0);
    }
    return $this->talk_count;
  }

  //生存フラグ判定
  public function IsLive($strict = false) {
    $dead = $this->IsDeadFlag($strict);
    return (null === $dead) ? $this->live == UserLive::LIVE : ! $dead;
  }

  //死亡フラグ判定
  public function IsDead($strict = false) {
    $dead = $this->IsDeadFlag($strict);
    return (null === $dead) ? ($this->live == UserLive::DEAD || $this->IsDrop()) : $dead;
  }

  //蘇生辞退フラグ判定
  public function IsDrop() {
    return $this->live == UserLive::DROP;
  }

  //行動不能判定
  public function IsInactive() {
    return $this->IsDead(true) || $this->IsOn(UserMode::REVIVE);
  }

  //同一ユーザ判定
  public function IsSame(User $user) {
    return $this === $user;
  }

  //同一名判定
  public function IsSameName($uname) {
    return $this->uname == $uname;
  }

  //自分と同一ユーザ判定
  public function IsSelf() {
    return $this->IsSame(DB::$SELF);
  }

  //身代わり君判定
  public function IsDummyBoy($strict = false) {
    return $this->IsSameName(GM::DUMMY_BOY) && ! ($strict && DB::$ROOM->IsQuiz());
  }

  //役職判定
  public function IsRole(...$target_list) {
    $role_list = $this->role_list;
    if (true === $target_list[0]) { //仮想役職対応
      array_shift($target_list);
      if (isset($this->virtual_role)) {
	$role_list[] = $this->virtual_role;
      }
    }
    $target_list = ArrayFilter::GetArg($target_list);

    if (count($target_list) > 1) {
      return count(array_intersect($target_list, $role_list)) > 0;
    } else {
      return in_array($target_list[0], $role_list);
    }
  }

  //役職グループ判定
  public function IsRoleGroup(...$target_list) {
    $role_list = $this->role_list;
    if (true === $target_list[0]) { //仮想役職対応
      array_shift($target_list);
      if (isset($this->virtual_role)) {
	$role_list[] = $this->virtual_role;
      }
    }
    $target_list = ArrayFilter::GetArg($target_list);

    foreach ($target_list as $target) {
      foreach ($role_list as $role) {
	if (Text::Search($role, $target)) {
	  return true;
	}
      }
    }
    return false;
  }

  //生存 + 役職判定
  public function IsLiveRole($role, $strict = false) {
    return $this->IsLive($strict) && $this->IsRole($role);
  }

  //生存 + 役職グループ判定
  public function IsLiveRoleGroup(...$target_list) {
    return $this->IsLive(true) && $this->IsRoleGroup($target_list);
  }

  //同一陣営判定
  public function IsCamp($camp) {
    return $this->GetCamp() == $camp;
  }

  //同一陣営判定 (勝利陣営)
  public function IsWinCamp($camp) {
    return $this->GetWinCamp() == $camp;
  }

  //同一陣営判定 (メイン役職限定)
  public function IsMainCamp($camp) {
    return $this->DistinguishCamp() == $camp;
  }

  //同一役職系判定
  public function IsMainGroup(...$target_list) {
    return in_array($this->DistinguishRoleGroup(), $target_list);
  }

  //拡張判定
  public function IsPartner($type, $target) {
    $partner = $this->GetPartner($type);
    if (null === $partner) {
      return false;
    }

    if (true === is_array($target)) {
      if (false === isset($target[$type])) {
	return false;
      }

      $target_list = $target[$type];
      if (true === is_array($target_list)) {
	return count(array_intersect($partner, $target_list)) > 0;
      } else {
	return false;
      }
    } else {
      return in_array($target, $partner);
    }
  }

  //能力喪失判定
  public function IsActive($role = null) {
    return ((null === $role) || $this->IsRole($role)) &&
      $this->IsOff(UserMode::LOST) && ! $this->IsRole('lost_ability');
  }

  //期間限定表示役職
  public function IsDoomRole($role) {
    return $this->IsRole($role) && DateBorder::On($this->GetDoomDate($role));
  }

  //所属陣営判別 (ラッパー)
  public function DistinguishCamp() {
    return RoleDataManager::GetCamp($this->main_role);
  }

  //所属役職グループ陣営判別 (ラッパー)
  public function DistinguishRoleGroup() {
    return RoleDataManager::GetGroup($this->main_role);
  }

  //有効シーン判定
  public function IsInvalidScene() {
    return $this->last_load_scene != DB::$ROOM->scene;
  }

  //投票済み判定
  public function ExistsVote() {
    if (DB::$ROOM->IsBeforeGame()) {
      return DB::$ROOM->Stack()->IsInclude('vote', $this->id);
    } else {
      return DB::$ROOM->Stack()->ExistsKey('vote', $this->id);
    }
  }

  //役職情報から表示情報を作成する
  public function GenerateRoleName($main_only = false) {
    $str = RoleDataHTML::Generate($this->main_role); //メイン役職
    if (true === $main_only) {
      return $str;
    }

    $role_count = $this->GetRoleList();
    if (count($role_count) < 2) { //サブ役職
      return $str;
    }

    $count = 1;
    foreach (RoleGroupSubData::$list as $class => $role_list) {
      foreach ($role_list as $sub_role) {
	if (false === $this->IsRole($sub_role)) {
	  continue;
	}

	switch ($sub_role) {
	case 'joker':
	  $css = RoleUser::IsJoker($this) ? $class : 'chiroptera';
	  break;

	case 'death_note':
	  $css = $this->IsDoomRole($sub_role) ? $class : 'chiroptera';
	  break;

	case 'male_status':
	  $css = RoleUser::GetSex($this, true) === Sex::MALE ? 'role-male' : $class;
	  break;

	case 'female_status':
	  $css = RoleUser::GetSex($this, true) === Sex::FEMALE ? 'role-female' : $class;
	  break;

	case 'gender_status':
	  switch (RoleUser::GetGenderStatus($this)) {
	  case Sex::MALE:
	    $css = 'role-male';
	    break;

	  case Sex::FEMALE:
	    $css = 'role-female';
	    break;

	  default:
	    $css = $class;
	    break;
	  }
	  break;

	default:
	  $css = $class;
	  break;
	}
	$str .= RoleDataHTML::Generate($sub_role, $css, true);
	if (++$count >= $role_count) {
	  break 2;
	}
      }
    }
    return $str;
  }

  //役職をパースして省略名を返す
  public function GenerateShortRoleName($heaven = false, $main_only = false) {
    if (empty($this->main_role)) {
      return;
    }

    if (isset($this->role_id)) { //キャッシュ判定
      if (true === $main_only && isset(DB::$USER->short_role_main[$this->role_id])) {
	return DB::$USER->short_role_main[$this->role_id];
      } elseif (isset(DB::$USER->short_role[$this->role_id])) {
	return DB::$USER->short_role[$this->role_id];
      }
    }

    //メイン役職を取得
    $camp = $this->GetCamp();
    $name = RoleDataManager::GetShortName($this->main_role);
    $str  = $camp == Camp::HUMAN ? $name : HTML::GenerateSpan($name, $camp);
    if (true === $main_only) {
      $str = $this->handle_name . HTML::GenerateSpan(' ' . Text::QuoteBracket($str), 'add-role');
      if (isset($this->role_id)) {
	DB::$USER->short_role_main[$this->role_id] = $str;
      }
      return $str;
    }

    //サブ役職を追加
    foreach (RoleDataManager::GetShortDiff($this->GetSubRoleList()) as $role => $name) {
      switch ($role) {
      case 'lovers':
      case 'challenge_lovers':
      case 'vega_lovers':
      case 'fake_lovers':
      case 'possessed_exchange':
      case 'letter_exchange':
	$str .= HTML::GenerateSpan($name, 'lovers');
	break;

      case 'infected':
      case 'psycho_infected':
	$str .= HTML::GenerateSpan($name, 'vampire');
	break;

      case 'rival':
      case 'enemy':
      case 'supported':
	$str .= HTML::GenerateSpan($name, 'duelist');
	break;

      default:
	$str .= $name;
	break;
      }
    }
    $uname = (true === $heaven) ? $this->uname : DB::$USER->TraceExchange($this->id)->uname;
    $css   = 'add-role';
    $str   = HTML::GenerateSpan(' ' . Text::QuoteBracket($str) . ' ' . Text::Quote($uname), $css);
    if (isset($this->role_id) && ! $this->IsRole('possessed_exchange')) {
      DB::$USER->short_role[$this->role_id] = $str;
    }
    return $str;
  }

  //発言数初期化処理
  public function InitializeTalkCount() {
    if (DB::$ROOM->IsTest()) {
      Text::p(sprintf('%d: %s', $this->id, $this->uname), '★Initialize Talk Count');
      return;
    }

    $list = [
      'room_no'    => DB::$ROOM->id,
      'user_no'    => $this->id,
      'date'       => DB::$ROOM->date,
      'talk_count' => 0
    ];

    return DB::Insert('user_talk_count', $list);
  }

  //個別 DB 更新処理
  public function Update($item, $value) {
    if (DB::$ROOM->IsTest()) {
      if (null === $value) {
	$value = 'NULL (reset)';
      }
      Text::p($value, sprintf('★Change [%s] (%s)', $item, $this->uname));
      return true;
    }

    $set = sprintf('%s = %s', $item, (null === $value) ? 'NULL' : "'{$value}'");
    return UserDB::Update($set, [], $this->id);
  }

  //更新処理
  public function UpdateList(array $list) {
    $stack     = [];
    $set_stack = [];
    foreach ($list as $key => $value) {
      $set_stack[] = sprintf('%s = ?', $key);
      $stack[] = $value;
    }
    return UserDB::Update(ArrayFilter::ToCSV($set_stack), $stack, $this->id);
  }

  //生存情報更新処理
  public function UpdateLive($live) {
    return $this->Update('live', $live);
  }

  //ID 更新処理 (KICK 後処理用)
  public function UpdateID($id) {
    if (DB::$ROOM->IsTest()) {
      Text::p(sprintf('%d -> %d: %s', $this->id, $id, $this->uname), '★Change ID');
      return;
    }

    return UserDB::UpdateID($id, $this->uname);
  }

  //player 更新処理
  public function UpdatePlayer() {
    if (false === isset($this->updated['role'])) {
      return true;
    }

    $role = $this->updated['role'];
    if (DB::$ROOM->IsTest()) {
      Text::p($role, sprintf('★Player (%s)', $this->uname));
      return true;
    }

    $list = [
      'room_no' => DB::$ROOM->id,
      'date'    => DB::$ROOM->date,
      'scene'   => DB::$ROOM->scene,
      'user_no' => $this->id,
      'role'    => $role
    ];

    if (false === DB::Insert('player', $list)) {
      return false;
    }
    return $this->Update('role_id', DB::GetInsertID());
  }

  //基幹死亡処理
  public function ToDead() {
    if ($this->IsDead(true)) {
      return false;
    }

    $this->UpdateLive(UserLive::DEAD);
    $this->Flag()->On(UserMode::DEAD);
    return true;
  }

  //蘇生処理
  public function Revive($virtual = false) {
    if ($this->IsLive(true)) {
      return false;
    }

    $this->UpdateLive(UserLive::LIVE);
    $this->Flag()->On(UserMode::REVIVE);
    if (false === $virtual) {
      DB::$ROOM->StoreDead($this->handle_name, DeadReason::REVIVE_SUCCESS);
    }
    return true;
  }

  //降参処理
  public function Fold() {
    if ($this->IsLive(true)) { //事前に死亡処理を実施しておく
      return false;
    }

    $this->UpdateLive(UserLive::FOLD);
    $this->Flag()->On(UserMode::FOLD);
    return true;
  }

  //役職更新処理
  public function ChangeRole($role) {
    $this->Update('role', $role);
    $this->updated['role'] = $role; //キャッシュ本体の更新は行わない
  }

  //役職置換処理
  public function ReplaceRole($target, $replace) {
    $this->ChangeRole(str_replace($target, $replace, $this->GetRole()));
  }

  //役職追加処理
  public function AddRole($role) {
    $base_role = $this->GetRole();
    if (in_array($role, Text::Parse($base_role))) { //同じ役職は追加しない
      return false;
    }
    $this->ChangeRole($base_role . ' ' . $role);
  }

  //仮想役職追加処理 (キャッシュ限定)
  public function AddVirtualRole($role) {
    if (false === in_array($role, $this->role_list)) {
      $this->role_list[] = $role;
    }
  }

  //メイン役職追加処理
  public function AddMainRole($role) {
    $this->ReplaceRole($this->main_role, $this->main_role . Text::QuoteBracket($role));
  }

  //死の宣告処理
  public function AddDoom($date, $role = 'death_warrant') {
    $this->AddRole(sprintf('%s[%d]', $role, DB::$ROOM->date + $date));
  }

  //能力喪失処理
  public function LostAbility() {
    $this->AddRole('lost_ability');
    $this->Flag()->On(UserMode::LOST);
  }

  //憑依解除処理
  public function ReturnPossessed($type) {
    $this->AddRole(sprintf('%s[%d-%d]', $type, DB::$ROOM->date + 1, $this->id));
  }

  //遺言登録
  public function StoreLastWords($handle_name = null) {
    if (false === $this->IsDummyBoy() && RoleUser::LimitedStoreLastWords($this)) { //スキップ判定
      return true;
    }

    if (null === $handle_name) {
      $handle_name = $this->handle_name;
    }
    if (DB::$ROOM->IsTest()) {
      Text::p(sprintf('%s (%s)', $handle_name, $this->uname), '★LastWords');
      return true;
    }

    $message = UserDB::GetLastWords($this->id);
    if (null === $message) {
      return true;
    }

    $list = [
      'room_no'     => DB::$ROOM->id,
      'date'        => DB::$ROOM->date,
      'handle_name' => $handle_name,
      'message'     => $message
    ];

    return DB::Insert('result_lastwords', $list);
  }

  //投票処理
  public function Vote($action, $target = null, $vote_number = null) {
    if (DB::$ROOM->IsTest()) {
      if (DB::$ROOM->IsDay()) {
	$stack = [
	  'user_no'     => $this->id,
	  'uname'       => $this->uname,
	  'target_no'   => $target,
	  'vote_number' => $vote_number
	];
	RQ::GetTest()->vote->day[$this->uname] = $stack;
	//Text::p($stack, '◆Vote');
      } else {
	Text::p(sprintf('%s: %s: %s', $action, $this->uname, $target), '★Vote');
      }
      return true;
    }

    $list = [
      'room_no'    => DB::$ROOM->id,
      'date'       => DB::$ROOM->date,
      'scene'      => DB::$ROOM->scene,
      'type'       => $action,
      'uname'      => $this->uname,
      'user_no'    => $this->id,
      'vote_count' => DB::$ROOM->vote_count
    ];
    if (isset($target)) {
      $list['target_no'] =$target;
    }
    if (isset($vote_number)) {
      $list['vote_number']  = $vote_number;
      $list['revote_count'] = RQ::Get()->revote_count;
    } else {
      //NULL 非許容なので初期値を設定する
      $list['revote_count'] = 0;
    }

    return DB::Insert('vote', $list);
  }

  //-- ログ処理用 --//
  //仮想役職リストの保存
  public function SaveRoleList() {
    $this->save_role_list = $this->role_list;
  }

  //仮想役職リストの初期化
  public function ResetRoleList() {
    $this->role_list = $this->save_role_list;
  }

  //デバッグ用
  public function p($data = null, $name = null) {
    Text::p((null === $data) ? $this : $this->$data, $name);
  }

  //-- private --//
  //仮想的な生死判定 (仮想なし > 突然死 > 降参 > 蘇生 > 死亡 > 変動なし)
  private function IsDeadFlag($strict = false) {
    if (false === $strict) {
      return null;
    } elseif ($this->IsOn(UserMode::SUICIDE)) {
      return true;
    } elseif ($this->IsOn(UserMode::FOLD)) {
      return true;
    } elseif ($this->IsOn(UserMode::REVIVE)) {
      return false;
    } elseif ($this->IsOn(UserMode::DEAD)) {
      return true;
    } else {
      return null;
    }
  }
}

//-- ユーザ情報ローダー --//
final class UserLoader extends stdClass {
  public $room_no;
  protected $rows = [];
  protected $kick = [];
  protected $name = [];
  protected $role = [];

  //-- インスタンス取得 --//
  public function __construct(Request $request, $lock = false) {
    $this->room_no = $request->room_no;
    $this->Load($request, $lock);
  }

  //-- プロパティ取得 --//
  //基礎データ取得
  public function Get() {
    return $this->rows;
  }

  //名前リスト取得
  public function GetName() {
    return $this->name;
  }

  //役職リスト取得
  public function GetRole() {
    return $this->role;
  }

  //-- ユーザ取得 --//
  //ユーザ名 -> ユーザ ID 変換
  public function UnameToNumber($uname) {
    return ArrayFilter::Get($this->name, $uname);
  }

  //ユーザ情報取得 (ユーザ ID 経由)
  public function ByID($id) {
    if (null === $id) {
      return new User();
    }

    $stack = $id > 0 ? $this->rows : $this->kick;
    return isset($stack[$id]) ? $stack[$id] : new User();
  }

  //ユーザ情報取得 (ユーザ名経由)
  public function ByUname($uname) {
    return $this->ByID($this->UnameToNumber($uname));
  }

  //ユーザ情報取得 (クッキー経由)
  public function BySession() {
    return $this->TraceExchange(Session::GetUser());
  }

  //ユーザ情報取得 (憑依先ユーザ ID 経由)
  public function ByVirtual($id) {
    return $this->TraceVirtual($id, 'possessed_target');
  }

  //ユーザ情報取得 (憑依元ユーザ ID 経由)
  public function ByReal($id) {
    return $this->TraceVirtual($id, 'possessed');
  }

  //ユーザ情報取得 (憑依先ユーザ名経由)
  public function ByVirtualUname($uname) {
    return $this->ByVirtual($this->UnameToNumber($uname));
  }

  //ユーザ情報取得 (憑依元ユーザ名経由)
  public function ByRealUname($uname) {
    return $this->ByReal($this->UnameToNumber($uname));
  }

  //交換憑依情報追跡
  public function TraceExchange($id) {
    $user = $this->ByID($id);
    $role = 'possessed_exchange';
    if (false === $user->IsRole($role) || false === DB::$ROOM->IsPlaying() ||
	(DB::$ROOM->IsOff(RoomMode::LOG) && $user->IsDead())) {
      return $user;
    }

    $stack = $user->GetPartner($role);
    return (is_array($stack) && DateBorder::Third()) ? $this->ByID(array_shift($stack)) : $user;
  }

  //HN 取得
  public function GetHandleName($uname, $virtual = false) {
    $user = (true === $virtual) ? $this->ByVirtualUname($uname) : $this->ByUname($uname);
    return property_exists($user, 'handle_name') ? $user->handle_name : '';
  }

  //身代わり君 ID 取得 (現状は固定値)
  public function GetDummyBoyID() {
    return GM::ID;
  }

  //生存者を取得する
  public function SearchLive($strict = false) {
    $stack = [];
    foreach ($this->rows as $user) {
      if ($user->IsLive($strict)) {
	$stack[$user->id] = $user->uname;
      }
    }
    return $stack;
  }

  //生存している人狼を取得する
  public function SearchLiveWolf() {
    $stack = [];
    foreach ($this->rows as $user) {
      if ($user->IsLive() && $user->IsMainGroup(CampGroup::WOLF)) {
	$stack[] = $user->id;
      }
    }
    return $stack;
  }

  //ユーザ数カウント
  public function Count() {
    return count($this->rows);
  }

  //全ユーザ数カウント
  public function CountAll() {
    return count($this->name);
  }

  //生存カウント
  public function CountLive() {
    return count($this->SearchLive());
  }

  //生存人狼カウント
  public function CountLiveWolf() {
    return count($this->SearchLiveWolf());
  }

  //出現妖狐カウント
  public function GetFoxCount() {
    $count = 0;
    foreach ($this->rows as $user) {
      if (RoleUser::IsFoxCount($user)) {
	$count++;
      }
    }
    return $count;
  }

  //特殊イベント情報セット
  public function SetEvent($force = false) {
    if (DB::$ROOM->id < 1) {
      return;
    }

    $event_list = DB::$ROOM->GetEvent($force);
    //Text::p($event_list, '◆Event [row]');
    if (false === is_array($event_list)) {
      return;
    }

    $stack = DB::$ROOM->Stack()->Get('event');
    foreach ($event_list as $event) {
      switch ($event['type']) {
      case EventType::WEATHER:
	$id = (int)$event['message'];
	$stack->On(WeatherManager::GetEvent($id));
	DB::$ROOM->Stack()->Set('weather', $id);
	break;

      case EventType::EVENT:
	$stack->On($event['message']);
	break;

      case EventType::VOTE_DUEL:
	RoleLoader::LoadMain($this->ByID($event['message']))->SetEvent();
	break;

      case EventType::SAME_FACE:
	$stack->On('same_face');
	DB::$ROOM->Stack()->Set('same_face', $event['message']);
	break;

      case DeadReason::BLIND_VOTE:
	$date = DB::$ROOM->date - (DB::$ROOM->IsDay() ? 1 : 0);
	$stack->Set('blind_vote', $date == $event['message']);
	break;
      }
    }

    EventManager::SetMultiple();
    //DB::$ROOM->Stack()->p('event', '◆EventStack');

    if (DB::$ROOM->IsDay()) { //昼限定
      EventManager::AddVirtualRole(true);
    }

    if (DB::$ROOM->IsPlaying()) { //昼夜両方
      EventManager::AddVirtualRole();
      EventManager::BadStatus();
    }
  }

  //霊界の配役公開判定
  /*
    非公開条件
    ・身代わり君は判定対象外
    ・天人存在 (能力発動前)
    ・イタコ生存 (投票前)
    ・時間差コピー能力者 (投票前 / 能力発動前)
    ・蘇生能力者生存
    ・イタコ/口寄せシステム発動中
  */
  public function IsOpenCast() {
    $evoke_scanner = [];
    $mind_evoke    = [];
    foreach ($this->rows as $user) {
      if ($user->IsDummyBoy()) {
	continue;
      }

      if ($user->IsRole('revive_priest')) {
	if ($user->IsActive()) {
	  return false;
	}
      } elseif ($user->IsRole('evoke_scanner')) {
	if ($user->IsLive()) {
	  if (DateBorder::One()) {
	    return false;
	  }
	  $evoke_scanner[] = $user->id;
	}
      } elseif (RoleUser::IsDelayCopy($user)) {
	//厳密には1日目の投票前に死亡した場合は公開可となるがレアケースなので対応しない
	if (DateBorder::One() || null !== $user->GetMainRoleTarget()) {
	  return false;
	}
      } elseif (RoleUser::IsRevive($user) || $user->IsRole('revive_mania')) {
	if ($user->IsLive()) {
	  return false;
	}
      }

      if ($user->IsRole('mind_evoke')) {
	ArrayFilter::AddMerge($mind_evoke, $user->GetPartner('mind_evoke'));
      }
    }
    return count(array_intersect($evoke_scanner, $mind_evoke)) < 1;
  }

  //仮想的な生死を返す
  public function IsVirtualLive($id, $strict = false) {
    //憑依されている場合は憑依者の生死を返す
    $real_user = $this->ByReal($id);
    if ($real_user->id != $id) {
      return $real_user->IsLive($strict);
    }

    //憑依先に移動している場合は常に死亡扱い
    if ($this->ByVirtual($id)->id != $id) {
      return false;
    }

    //憑依が無ければ本人の生死を返す
    return $this->ByID($id)->IsLive($strict);
  }

  //死亡処理
  public function Kill($id, $reason, $type = null) {
    $user = $this->ByReal($id);
    if (false === $user->ToDead()) {
      return false;
    }

    $virtual = $this->ByVirtual($user->id);
    DB::$ROOM->StoreDead($virtual->handle_name, $reason, $type);

    switch ($reason) {
    case DeadReason::NOVOTED:
    case DeadReason::SILENCE:
    case DeadReason::POSSESSED_TARGETED:
      break;

    default: //遺言処理
      $user->StoreLastWords($virtual->handle_name);
      if (false === $virtual->IsSame($user)) {
	$virtual->StoreLastWords();
      }
      break;
    }
    return true;
  }

  //突然死処理
  public function SuddenDeath($id, $reason, $type = null) {
    if (false === $this->Kill($id, $reason, $type)) {
      return false;
    }

    $user = $this->ByReal($id);
    $user->Flag()->On(UserMode::SUICIDE);

    switch ($reason) {
    case DeadReason::NOVOTED:
    case DeadReason::SILENCE:
    case DeadReason::FORCE_SUDDEN_DEATH:
      $str = strtolower($reason);
      break;

    default:
      $str = 'sudden_death';
      break;
    }

    RoomTalk::StoreSystem($user->GetName() . ' ' . DeadMessage::$$str);
    return true;
  }

  //-- 役職関連 --//
  //希望役職取得
  public function GetWishRole($uname) {
    return $this->ByUname($uname)->role;
  }

  //役職ユーザ ID 取得
  public function GetRoleID($role) {
    return ArrayFilter::GetList($this->role, $role);
  }

  //役職ユーザ数取得
  public function CountRole($role) {
    return count($this->GetRoleID($role));
  }

  //役職ユーザ取得
  public function GetRoleUser($role) {
    $stack = [];
    foreach ($this->GetRoleID($role) as $id) {
      $stack[] = $this->ByID($id);
    }
    return $stack;
  }

  //役職の出現判定
  public function IsAppear(...$role_list) {
    return count(array_intersect($role_list, array_keys($this->role))) > 0;
  }

  //役職の生存判定
  public function IsLiveRole($role) {
    if (false === $this->IsAppear($role)) { //存在判定
      return false;
    }

    foreach ($this->GetRoleUser($role) as $user) {
      if ($user->IsLive(true)) {
	return true;
      }
    }
    return false;
  }

  //-- ログ処理用 --//
  //仮想役職リストの保存
  public function SaveRoleList() {
    foreach ($this->rows as $user) {
      $user->SaveRoleList();
    }
  }

  //仮想役職リストの初期化
  public function ResetRoleList() {
    foreach ($this->rows as $user) {
      $user->ResetRoleList();
    }
  }

  //player の復元
  public function ResetPlayer() {
    if (false === isset($this->player->user_list)) {
      return;
    }

    foreach ($this->player->user_list as $id => $stack) {
      $this->ByID($id)->ChangePlayer(max($stack));
    }
  }

  //-- 投票処理用 --//
  //KICK の後処理
  public function UpdateKick() {
    $id = 1;
    foreach ($this->rows as $user) {
      if ($user->id != $id) {
	$user->UpdateID($id);
	$user->id = $id;
      }
      $id++;
    }

    foreach ($this->kick as $user) {
      $user->UpdateID(-1);
    }
  }

  //ゲーム開始処理
  public function GameStart() {
    $flag = OptionManager::Exists('initialize_talk_count');
    foreach ($this->rows as $user) {
      $user->UpdatePlayer();
      if (true === $flag) {
	$user->InitializeTalkCount();
      }
    }
  }

  //-- private --//
  //村情報ロード
  private function Load(Request $request, $lock = false) {
    if ($request->IsVirtualRoom()) { //仮想モード
      $user_list = $request->GetTest()->test_users;
    } elseif (isset($request->retrieve_type)) { //特殊モード
      switch ($request->retrieve_type) {
      case 'entry_user': //入村処理
	$user_list = UserLoaderDB::LoadEntryUser($request->room_no);
	break;

      case RoomScene::BEFORE: //ゲーム開始前
	$user_list = UserLoaderDB::LoadBeforegame($request->room_no);
	break;

      case RoomScene::DAY: //昼 + 下界
	$user_list = UserLoaderDB::LoadDay($request->room_no);
	break;
      }
    } else {
      $user_list = UserLoaderDB::Load($request->room_no, $lock);
    }
    $this->Parse($user_list);
  }

  //ユーザ情報を User クラスでパースして登録
  private function Parse(array $user_list) {
    //初期化処理
    $this->rows = [];
    $this->kick = [];
    $this->name = [];
    $this->role = [];
    $kick = 0;

    foreach ($user_list as $user) {
      $user->Parse();
      if ($user->id < 0 || $user->live == UserLive::KICK) { //KICK 判定
	$this->kick[$user->id = --$kick] = $user;
      } else {
	$this->rows[$user->id] = $user;
	foreach ($user->GetRoleList() as $role) {
	  if (! empty($role)) {
	    $this->role[$role][] = $user->id;
	  }
	}
      }
      $this->name[$user->uname] = $user->id;
    }
  }

  //憑依情報追跡
  private function TraceVirtual($id, $type) {
    $user = $this->ByID($id);
    if (false === DB::$ROOM->IsPlaying()) {
      return $user;
    }

    switch ($type) {
    case 'possessed':
      if (false === $user->IsRole($type)) {
	return $user;
      }
      break;

    default:
      if (false === RoleUser::IsPossessed($user)) {
	return $user;
      }
      break;
    }

    $target_id = $user->GetPossessedTarget($type, DB::$ROOM->date);
    return $target_id === false ? $user : $this->ByID($target_id);
  }
}
