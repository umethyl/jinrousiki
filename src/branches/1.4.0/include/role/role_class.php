<?php
//-- 役職コントローラークラス --//
class RoleManager{
  var $path;
  var $loaded;
  var $actor;

  //発言表示
  var $talk_list = array('blinder', 'earplug', 'speaker');

  //発言変換
  var $say_list = array('rainbow', 'weekly', 'passion', 'actor', 'grassy', 'invisible', 'mower',
			'silent', 'side_reverse', 'line_reverse');

  //声量
  var $voice_list = array('strong_voice', 'normal_voice', 'weak_voice', 'inside_voice',
			  'outside_voice', 'upper_voice', 'downer_voice', 'random_voice');

  //処刑投票
  var $vote_do_list = array('authority', 'critical_voter', 'random_voter', 'watcher', 'panelist');

  //処刑得票
  var $voted_list = array('upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck',
			  'random_luck');

  //処刑投票系能力者
  var $vote_ability_list = array('saint', 'executor', 'agitate_mad', 'quiz', 'impatience',
				 'authority', 'rebel', 'decide', 'plague', 'good_luck', 'bad_luck');

  //反逆者判定
  var $rebel_list = array('rebel');

  //処刑者決定 (順番依存あり)
  var $vote_kill_list = array('decide', 'bad_luck', 'impatience', 'good_luck', 'plague',
			      'quiz', 'executor', 'saint', 'agitate_mad');

  //ショック死
  var $sudden_death_list = array('febris', 'frostbite', 'death_warrant', 'chicken', 'rabbit',
				 'perverseness', 'flattery', 'impatience', 'celibacy', 'nervy',
				 'androphobia', 'gynophobia', 'panelist');

  //特殊毒能力者
  var $poison_list = array('strong_poison', 'incubate_poison', 'guide_poison', 'dummy_poison',
			   'poison_jealousy', 'poison_doll', 'poison_wolf', 'poison_fox',
			   'poison_chiroptera', 'poison_ogre');

  //鬼陣営
  var $ogre_list = array('ogre', 'orange_ogre', 'indigo_ogre', 'poison_ogre', 'west_ogre',
			 'east_ogre', 'north_ogre', 'south_ogre', 'incubus_ogre', 'power_ogre',
			 'revive_ogre', 'sacrifice_ogre', 'yaksa', 'succubus_yaksa',
			 'dowser_yaksa');

  function RoleManager(){ $this->__construct(); }
  function __construct(){
    $this->path = JINRO_INC . '/role';
    $this->loaded->file = array();
    $this->loaded->class = array();
  }

  function Load($type, $shift = false){
    $stack = array();
    foreach($this->GetList($type) as $role){
      if(! $this->actor->IsRole($role)) continue;
      $stack[] = $role;
      $this->LoadFile($role);
      $this->LoadClass($role, 'Role_' . $role);
    }
    $filter = $this->GetFilter($stack);
    return $shift ? array_shift($filter) : $filter;
  }

  function LoadFile($name){
    if(is_null($name) || in_array($name, $this->loaded->file)) return false;
    require_once($this->path . '/' . $name . '.php');
    $this->loaded->file[] = $name;
    return true;
  }

  function LoadClass($name, $class){
    if(is_null($name) || in_array($name, $this->loaded->class)) return false;
    $this->loaded->class[$name] = new $class();
    return true;
  }

  function LoadFilter($type){
    return $this->GetFilter($this->GetList($type));
  }

  function GetList($type){
    $name = $type . '_list';
    $stack = $this->$name;
    return is_array($stack) ? $stack : array();
  }

  function GetFilter($list){
    $stack = array();
    foreach($list as $key){ //順番依存があるので配列関数を使わないで処理する
      if(is_object(($class = $this->loaded->class[$key]))) $stack[] = $class;
    }
    return $stack;
  }

  function GetWhisperingUserInfo($role, &$class){
    global $ROOM, $SELF;

    if($SELF->IsRole('deep_sleep')) return false; //爆睡者にはいっさい見えない
    switch($role){
    case 'common': //共有者のささやき
      if($SELF->IsRole('dummy_common')) return false; //夢共有者には見えない
      $class = 'talk-common';
      return '共有者の小声';

    case 'wolf': //人狼の遠吠え
      if($SELF->IsRole('mind_scanner')) return false; //さとりには見えない
      return '狼の遠吠え';

    case 'lovers': //恋人の囁き
      return '恋人の囁き';
    }
    return false;
  }

  function GetWhisperingSound($role, $talk, &$class){
    global $MESSAGE;

    switch($role){
    case 'common':
      $class = 'say-common';
      return $MESSAGE->common_talk;

    case 'wolf':
      return $MESSAGE->wolf_howl;

    case 'lovers':
      return $MESSAGE->lovers_talk;
    }
  }
}

//-- 役職の基底クラス --//
class Role{
  function Role(){ $this->__construct(); }
  function __construct(){}

  //-- 判定用関数 --//
  function Ignored(){
    global $ROOM, $USERS, $ROLES;
    //return false; //テスト用
    return ! $ROOM->IsPlaying() ||
      ! ($USERS->IsVirtualLive($ROLES->actor->user_no) || $ROLES->actor->virtual_live);
  }

  function IsSameUser($uname){
    global $ROLES;
    return $ROLES->actor->IsSame($uname);
  }

  function IsLive($strict = false){
    global $ROLES;
    return $ROLES->actor->IsLive($strict);
  }

  function IsDead($strict = false){
    global $ROLES;
    return $ROLES->actor->IsDead($strict);
  }
}

//-- 発言フィルタリング用拡張クラス --//
class RoleTalkFilter extends Role{
  var $volume_list = array('weak', 'normal', 'strong');

  function RoleTalkFilter(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function AddTalk($user, $talk, &$user_info, &$volume, &$sentence){}
  function AddWhisper($role, $talk, &$user_info, &$volume, &$sentence){}

  function ChangeVolume($type, &$volume, &$sentence){
    global $MESSAGE;

    if($this->Ignored()) return;
    switch($type){
    case 'up':
      if(($key = array_search($volume, $this->volume_list)) === false) return;
      if(++$key >= count($this->volume_list))
	$sentence = $MESSAGE->howling;
      else
	$volume = $this->volume_list[$key];
      break;

    case 'down':
      if(($key = array_search($volume, $this->volume_list)) === false) return;
      if(--$key < 0)
	$sentence = $MESSAGE->common_talk;
      else
	$volume = $this->volume_list[$key];
      break;
    }
  }
}

//-- 処刑投票能力者用拡張クラス --//
class RoleVoteAbility extends Role{
  var $role;
  var $data_type;
  var $decide_type;

  function RoleVoteAbility(){ $this->__construct(); }
  function __construct(){
    parent::__construct();
    $this->role = array_pop(explode('Role_', get_class($this)));
  }

  function SetVoteAbility($uname){
    global $ROLES, $USERS;
    switch($this->data_type){
    case 'self':
      $ROLES->stack->{$this->role} = $ROLES->actor->uname;
      break;

    case 'target':
      $ROLES->stack->{$this->role} = $uname;
      break;

    case 'both':
      $ROLES->stack->{$this->role} = $ROLES->actor->uname;
      $ROLES->stack->{$this->role . '_uname'} = $uname;
      break;

    case 'array':
      $user = $USERS->ByRealUname($ROLES->actor->uname);
      if($user->IsRole($this->role)) $ROLES->stack->{$this->role}[] = $user->uname;
      break;
    }
  }

  function DecideVoteKill(&$uname){
    global $ROLES;

    if($uname != '') return true;
    switch($this->decide_type){
    case 'decide':
      $target = $ROLES->stack->{$this->role};
      if(in_array($target, $ROLES->stack->vote_possible)) $uname = $target;
      return true;

    case 'escape':
      $key = array_search($ROLES->stack->{$this->role}, $ROLES->stack->vote_possible);
      if($key === false) return false;
      unset($ROLES->stack->vote_possible[$key]);
      if(count($ROLES->stack->vote_possible) == 1){ //候補が一人になった場合は処刑者決定
	$uname = array_shift($ROLES->stack->vote_possible);
	return true;
      }
      return false;

    default:
      return false;
    }
  }
}
