<?php
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
