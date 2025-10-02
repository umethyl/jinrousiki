<?php
//-- テスト村生成クラス --//
class DevRoom {
  //テスト村データ初期化
  static function Initialize($list = array()) {
    //初期村データを生成
    RQ::Set('room_no', 1);
    RQ::Set('vote_times', 1);
    RQ::Set('reverse_log', null);
    $base_list = array(
     'id' => RQ::Get()->room_no, 'comment' => '',
     'date' => 0, 'scene' => 'beforegame', 'status' => 'waiting',
     'game_option' => 'dummy_boy real_time:6:4 wish_role',
     'option_role' => '', 'vote_count' => 1
    );

    RQ::InitTestRoom();
    RQ::GetTest()->test_room = array_merge($base_list, $list);
    RQ::GetTest()->event           = array();
    RQ::GetTest()->result_ability  = array();
    RQ::GetTest()->result_dead     = array();
    RQ::GetTest()->system_message  = array();
  }

  //村データロード
  static function Load() {
    DB::$ROOM = new Room(RQ::Get());
    DB::$ROOM->test_mode    = true;
    DB::$ROOM->log_mode     = true;
    DB::$ROOM->scene        = 'beforegame';
    DB::$ROOM->revote_count = 0;
    if (! isset(DB::$ROOM->vote)) DB::$ROOM->vote = array();
  }

  //イベント情報取得
  static function GetEvent() {
    $stack = array();
    foreach (RQ::GetTest()->system_message as $date => $date_list) {
      //Text::p($date_list, $date);
      if ($date != DB::$ROOM->date) continue;
      foreach ($date_list as $type => $type_list) {
	switch ($type) {
	case 'WEATHER':
	case 'EVENT':
	case 'SAME_FACE':
	case 'VOTE_DUEL':
	case 'BLIND_VOTE':
	  foreach ($type_list as $event) {
	    $stack[] = array('type' => $type, 'message' => $event);
	  }
	  break;
	}
      }
    }
    //Text::p($stack);
    return $stack;
  }

  //能力発動結果取得
  static function GetAbility($date, $action, $limit) {
    $stack = RQ::GetTest()->result_ability;
    $stack = array_key_exists($date,   $stack) ? $stack[$date]   : array();
    $stack = array_key_exists($action, $stack) ? $stack[$action] : array();
    if ($limit) {
      $limit_stack = array();
      foreach ($stack as $list) {
	if ($list['user_no'] == DB::$SELF->id) $limit_stack[] = $list;
      }
      $stack = $limit_stack;
    }
    return $stack;
  }

  //配役テスト
  static function Cast(StdClass $stack) {
    RQ::SetTestRoom('game_option', implode(' ', $stack->game_option));
    RQ::SetTestRoom('option_role', implode(' ', $stack->option_role));

    DB::$ROOM = new Room(RQ::Get());
    DB::$ROOM->LoadOption();
    //Text::p(DB::$ROOM);

    $user_count = RQ::Get()->user_count;
    $try_count  = RQ::Get()->try_count;
    $str = '%0' . strlen($try_count) . 'd回目: ';
    for ($i = 1; $i <= $try_count; $i++) {
      printf($str, $i);
      $role_list = Cast::Get($user_count);
      if ($role_list == '') break;
      Text::p(Vote::GenerateRoleNameList(array_count_values($role_list), true));
    }
  }
}

//-- テストユーザ生成クラス --//
class DevUser {
  // ユーザのアイコンカラーリスト
  static $icon_color_list = array('#DDDDDD', '#999999', '#FFD700', '#FF9900', '#FF0000',
				  '#99CCFF', '#0066FF', '#00EE00', '#CC00CC', '#FF9999');

  // ユーザの初期データ
  static $user_list = array(
     1 => array('uname'         => 'dummy_boy',
		'handle_name'   => '身代わり君',
		'icon_filename' => '../img/dummy_boy_user_icon.jpg',
		'color'         => '#000000'),
     2 => array('uname'         => 'light_gray',
		'handle_name'   => '明灰'),
     3 => array('uname'         => 'dark_gray',
		'handle_name'   => '暗灰'),
     4 => array('uname'         => 'yellow',
		'handle_name'   => '黄色'),
     5 => array('uname'         => 'orange',
		'handle_name'   => 'オレンジ'),
     6 => array('uname'         => 'red',
		'handle_name'   => '赤'),
     7 => array('uname'         => 'light_blue',
		'handle_name'   => '水色'),
     8 => array('uname'         => 'blue',
		'handle_name'   => '青'),
     9 => array('uname'         => 'green',
		'handle_name'   => '緑'),
    10 => array('uname'         => 'purple',
		'handle_name'   => '紫'),
    11 => array('uname'         => 'cherry',
		'handle_name'   => 'さくら'),
    12 => array('uname'         => 'white',
		'handle_name'   => '白'),
    13 => array('uname'         => 'black',
		'handle_name'   => '黒'),
    14 => array('uname'         => 'gold',
		'handle_name'   => '金'),
    15 => array('uname'         => 'frame',
		'handle_name'   => '炎'),
    16 => array('uname'         => 'scarlet',
		'handle_name'   => '紅'),
    17 => array('uname'         => 'ice',
		'handle_name'   => '氷'),
    18 => array('uname'         => 'deep_blue',
		'handle_name'   => '蒼'),
    19 => array('uname'         => 'emerald',
		'handle_name'   => '翠'),
    20 => array('uname'         => 'rose',
		'handle_name'   => '薔薇'),
    21 => array('uname'         => 'peach',
		'handle_name'   => '桃'),
    22 => array('uname'         => 'gust',
		'handle_name'   => '霧'),
    23 => array('uname'         => 'cloud',
		'handle_name'   => '雲'),
    24 => array('uname'         => 'moon',
		'handle_name'   => '月'),
    25 => array('uname'         => 'sun',
		'handle_name'   => '太陽'),
			    );

  //ユーザデータ初期化
  static function Initialize($count, $role_list = array()) {
    RQ::GetTest()->test_users = array();
    for ($id = 1; $id <= $count; $id++) {
      RQ::GetTest()->test_users[$id] = new User(isset($role_list[$id]) ? $role_list[$id] : null);
    }

    foreach (self::$user_list as $id => $list) {
      if ($id > $count) break;
      foreach ($list as $key => $value) {
	RQ::GetTest()->test_users[$id]->id   = $id;
	RQ::GetTest()->test_users[$id]->$key = $value;
      }
    }
  }

  //ユーザデータ補完
  static function Complement($scene = 'beforegame') {
    foreach (RQ::GetTest()->test_users as $id => $user) {
      $user->room_no = RQ::Get()->room_no;
      $user->role_id = $id;
      if (! isset($user->live))    $user->live    = 'live';
      if (! isset($user->sex))     $user->sex     = $id % 2 == 0 ? 'female' : 'male';
      if (! isset($user->profile)) $user->profile = $id;
      $user->last_load_scene = $scene;
      if ($id > 1) {
	$user->color = self::$icon_color_list[($id - 2) % 10];
	$user->icon_filename = sprintf('%03d.gif', ($id - 2) % 10 + 1);
      }
    }
  }

  //ユーザ情報をロード
  static function Load() {
    DB::$USER = new UserData(RQ::Get());
    DB::$SELF = DB::$USER->ByID(1);
    if (DB::$ROOM->IsBeforeGame()) {
      foreach (DB::$USER->rows as $user) {
	if (! isset($user->vote_type)) $user->vote_type = 'GAME_START';
      }
    }
  }
}

//-- HTML 生成クラス (テスト拡張) --//
class DevHTML {
  //共通リクエストロード
  static function LoadRequest() {
    Loader::LoadRequest();
    RQ::Get()->ParsePostOn('execute');
  }

  static function IsExecute() {
    return RQ::Get()->execute;
  }

  // フォームヘッダ出力
  static function OutputFormHeader($title, $url) {
    self::LoadRequest();
    HTML::OutputHeader($title, 'test/role', true);
    foreach (array('user_count' => 20, 'try_count' => 100) as $key => $value) {
      RQ::Get()->ParsePostInt($key);
      $$key = RQ::Get()->$key > 0 ? RQ::Get()->$key : $value;
    }
    $id_u = 'user_count';
    $id_t = 'try_count';
    echo <<<EOF
<form method="post" action="{$url}">
<input type="hidden" name="execute" value="on">
<label for="{$id_u}">人数</label><input type="text" id="{$id_u}" name="{$id_u}" size="2" value="{$$id_u}">
<label for="{$id_t}">試行回数</label><input type="text" id="{$id_t}" name="{$id_t}" size="2" value="{$$id_t}">
<input type="submit" value=" 実 行 "><br>

EOF;
  }

  //前日の能力発動結果出力
  static function OutputAbilityAction() {
    //昼間で役職公開が許可されているときのみ表示
    if (! DB::$ROOM->IsDay() || ! (DB::$SELF->IsDummyBoy() || DB::$ROOM->IsOpenCast())) {
      return false;
    }

    $header = '<b>前日の夜、%s ';
    $footer = '</b>' . Text::BRLF;
    foreach (RQ::GetTest()->vote->night as $stack) {
      printf($header, DB::$USER->ByID($stack['user_no'])->GenerateShortRoleName(false, true));
      $target = '';
      switch ($stack['type']) {
      case 'CUPID_DO':
      case 'STEP_MAGE_DO':
      case 'STEP_GUARD_DO':
      case 'SPREAD_WIZARD_DO':
      case 'STEP_WOLF_EAT':
      case 'SILENT_WOLF_EAT':
      case 'STEP_DO':
      case 'STEP_VAMPIRE_DO':
	$target_stack = array();
	foreach (explode(' ', $stack['target_no']) as $id) {
	  $user = DB::$USER->ByVirtual($id);
	  $target_stack[$user->id] = $user->GenerateShortRoleName(false, true);
	}
	ksort($target_stack);
	$target = implode(' ', $target_stack);
	break;

      default:
	if (isset($stack['target_no'])) {
	  $target = DB::$USER->ByVirtual($stack['target_no'])->GenerateShortRoleName(false, true);
	}
	break;
      }
      if (! empty($target)) printf('は %s', $target);

      switch ($stack['type']) {
      case 'GUARD_DO':
      case 'REPORTER_DO':
      case 'ASSASSIN_DO':
      case 'WIZARD_DO':
      case 'ESCAPE_DO':
      case 'WOLF_EAT':
      case 'DREAM_EAT':
      case 'STEP_DO':
      case 'CUPID_DO':
      case 'VAMPIRE_DO':
      case 'FAIRY_DO':
      case 'OGRE_DO':
      case 'DUELIST_DO':
      case 'DEATH_NOTE_DO':
      case 'ASSASSIN_NOT_DO':
      case 'POSSESSED_NOT_DO':
      case 'OGRE_NOT_DO':
      case 'DEATH_NOTE_NOT_DO':
	echo Message::${strtolower($stack['type'])};
	break;

      case 'POISON_CAT_DO':
	echo Message::$revive_do;
	break;

      case 'POISON_CAT_NOT_DO':
	echo Message::$revive_not_do;
	break;

      case 'SPREAD_WIZARD_DO':
	echo Message::$wizard_do;
	break;

      case 'TRAP_MAD_DO':
	echo Message::$trap_do;
	break;

      case 'TRAP_MAD_NOT_DO':
	echo Message::$trap_not_do;
	break;

      case 'MAGE_DO':
      case 'STEP_MAGE_DO':
      case 'CHILD_FOX_DO':
	echo 'を占いました';
	break;

      case 'VOODOO_KILLER_DO':
	echo 'の呪いを祓いました';
	break;

      case 'STEP_GUARD_DO':
	echo Message::$guard_do;
	break;

      case 'ANTI_VOODOO_DO':
	echo 'の厄を祓いました';
	break;

      case 'MIND_SCANNER_DO':
	echo 'の心を読みました';
	break;

      case 'JAMMER_MAD_DO':
	echo 'の占いを妨害しました';
	break;

      case 'VOODOO_MAD_DO':
      case 'VOODOO_FOX_DO':
	echo 'に呪いをかけました';
	break;

      case 'STEP_WOLF_EAT':
      case 'SILENT_WOLF_EAT':
      case 'POSSESSED_DO':
      case 'STEP_VAMPIRE_DO':
	echo 'を狙いました';
	break;

      case 'MANIA_DO':
	echo 'を真似しました';
	break;
      }
      echo $footer;
    }
  }
}
