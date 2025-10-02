<?php
//-- イベントローダー --//
class EventLoader {
  const PATH = '%s/event/%s.php';
  const CLASS_PREFIX = 'Event_';
  private static $file  = array();
  private static $class = array();

  //イベントロード
  public static function Load($name) {
    return (self::LoadFile($name) && self::LoadClass($name)) ? self::$class[$name] : null;
  }

  //ファイルロード
  public static function LoadFile($name) {
    return LoadManager::LoadFile(self::$file, $name, self::GetPath($name));
  }

  //クラスロード
  private static function LoadClass($name) {
    return LoadManager::LoadClass(self::$class, $name, self::CLASS_PREFIX);
  }

  //ファイルパス取得
  private static function GetPath($name) {
    return sprintf(self::PATH, JINROU_INC, $name);
  }
}

//-- イベントマネージャ --//
class EventManager {
  //複合型イベントセット
  public static function SetMultiple() {
    self::Filter('multiple', 'SetEvent');
  }

  //仮想役職セット
  public static function AddVirtualRole($day = false) {
    $method = __FUNCTION__;
    foreach (self::Get($day ? 'virtual_role_day' : 'virtual_role') as $event) {
      EventLoader::Load($event)->$method();
    }
  }

  //仮想役職セット (悪戯)
  public static function BadStatus() {
    $method = __FUNCTION__;
    foreach (EventFilterData::$bad_status as $role) {
      if (DB::$USER->IsAppear($role)) {
	RoleLoader::Load($role)->$method();
      }
    }
  }

  //決選投票判定
  public static function VoteDuel() {
    self::Filter('vote_duel', __FUNCTION__);
  }

  //処刑投票数補正
  public static function VoteDo() {
    self::Filter('vote_do', __FUNCTION__);
  }

  //処刑投票妨害
  public static function VoteKillAction() {
    self::Filter('vote_kill_action', __FUNCTION__);
  }

  //処刑者決定
  public static function DecideVoteKill() {
    if (! RoleManager::Stack()->IsEmpty('vote_kill_uname')) return;

    $method = __FUNCTION__;
    foreach (EventFilterData::$decide_vote_kill as $event) {
      if (DB::$ROOM->IsOption($event) || DB::$ROOM->IsEvent($event)) {
	OptionLoader::Load($event)->$method();
      }
    }
  }

  //夜投票封印
  public static function SealVoteNight() {
    $method = __FUNCTION__;
    $stack  = array();
    foreach (self::Get('seal_vote_night') as $event) {
      EventLoader::Load($event)->$method($stack);
    }
    return $stack;
  }

  //足音
  public static function Step() {
    self::Filter('step', __FUNCTION__);
  }

  //罠能力有効
  public static function IsSetTrap() {
    foreach (EventFilterData::$ignore_set_trap as $event) {
      if (DB::$ROOM->IsEvent($event)) return false;
    }
    return true;
  }

  //神隠し
  public static function TenguKill() {
    self::Filter('tengu_kill', __FUNCTION__);
  }

  //悪戯 (妖精)
  public static function FairyMage() {
    self::Filter('fairy_mage', __FUNCTION__);
  }

  //適合イベント取得
  private static function Get($type) {
    $stack = array();
    foreach (EventFilterData::$$type as $event) {
      if (DB::$ROOM->IsEvent($event)) {
	$stack[] = $event;
      }
    }
    return $stack;
  }

  //共通処理
  private static function Filter($type, $method) {
    foreach (self::Get($type) as $event) {
      EventLoader::Load($event)->$method();
    }
  }
}

//-- イベント基底クラス --//
abstract class Event {
  public function __construct() {
    $this->name = Text::Cut(get_class($this), EventLoader::CLASS_PREFIX);
  }
}
