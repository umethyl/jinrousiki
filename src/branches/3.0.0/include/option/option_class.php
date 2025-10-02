<?php
//-- オプションマネージャ --//
class OptionManager {
  const PATH = '%s/option/%s.php';
  private static $file  = array();
  private static $class = array();
  private static $stack; //スタックデータ

  //特殊普通村編成リスト
  private static $role_list = array(
    'detective', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid',
    'medium', 'mania'
  );

  //特殊サブ配役リスト
  private static $cast_list = array(
    'decide', 'authority', 'joker', 'deep_sleep', 'blinder', 'mind_open', 'perverseness',
    'liar', 'gentleman', 'passion', 'critical', 'sudden_death', 'quiz'
  );

  //ファイルロード
  static function Load($name) {
    if (is_null($name) || ! file_exists($file = self::GetPath($name))) return false;
    if (in_array($name, self::$file)) return true;
    require_once($file);
    self::$file[] = $name;
    return true;
  }

  //スタックロード
  static function LoadStack() {
    self::$stack = new Stack();
  }

  //スタック取得
  static function Stack() {
    return self::$stack;
  }

  //クラス取得
  static function GetClass($name) {
    return self::Load($name) ? self::LoadClass($name) : null;
  }

  // 村オプション変更判定
  static function IsChange() {
    if (is_null(self::$stack)) {
      self::LoadStack();
      self::Stack()->Set('change', false);
    }
    return self::Stack()->Get('change');
  }

  //特殊普通村の配役処理
  static function SetRole(array &$list, $count) {
    foreach (self::$role_list as $option) {
      if (DB::$ROOM->IsOption($option) && self::Load($option)) {
	self::LoadClass($option)->SetRole($list, $count);
      }
    }
  }

  //ユーザ配役処理
  static function Cast() {
    $delete = self::Stack()->Get('delete');
    foreach (self::$cast_list as $option) {
      if (DB::$ROOM->IsOption($option) && self::Load($option)) {
	$stack = self::LoadClass($option)->Cast();
	if (is_array($stack)) $delete = array_merge($delete, $stack);
      }
    }
    self::Stack()->Set('delete', $delete);
  }

  //役職置換処理
  static function Replace(array &$list, $base, $target) {
    if (! isset($list[$base]) || $list[$base] < 1) return false;
    $list[$base]--;
    isset($list[$target]) ? $list[$target]++ : $list[$target] = 1;
    return true;
  }

  //希望役職リスト取得
  static function GetWishRole() {
    $stack = array('none');
    if (DB::$ROOM->IsChaosWish()) {
      $stack = array_merge($stack, RoleData::GetGroupList());
    }
    elseif (DB::$ROOM->IsOption('gray_random')) {
      array_push($stack, 'human', 'wolf', 'mad', 'fox');
    }
    else {
      array_push($stack, 'human', 'wolf');
      if (DB::$ROOM->IsQuiz()) {
	array_push($stack, 'mad', 'common', 'fox');
      }
      else {
	array_push($stack, 'mage', 'necromancer', 'mad', 'guard', 'common');
	if (DB::$ROOM->IsOption('detective')) $stack[] = 'detective_common';
	$stack[] = 'fox';
      }
      foreach (array('poison', 'assassin', 'boss_wolf', 'depraver') as $role) {
	if (DB::$ROOM->IsOption($role)) $stack[] = $role;
      }
      if (DB::$ROOM->IsOption('poison_wolf')) array_push($stack, 'poison_wolf', 'pharmacist');
      foreach (array('possessed_wolf', 'sirius_wolf', 'child_fox', 'cupid') as $role) {
	if (DB::$ROOM->IsOption($role)) $stack[] = $role;
      }
      if (DB::$ROOM->IsOption('medium')) array_push($stack, 'medium', 'mind_cupid');
      if (DB::$ROOM->IsOptionGroup('mania') && ! in_array('mania', $stack)) $stack[] = 'mania';
    }
    return $stack;
  }

  //オプション名生成
  static function GenerateCaption($name) {
    return self::Load($name) ? self::LoadClass($name)->GetName() : '';
  }

  //オプション名出力
  static function OutputCaption($name) {
    echo self::GenerateCaption($name);
  }

  //オプション説明出力
  static function OutputExplain($name) {
    echo self::Load($name) ? self::LoadClass($name)->GetExplain() : '';
  }

  //クラスロード
  private static function LoadClass($name) {
    if (! isset(self::$class[$name])) {
      $class_name = 'Option_' . $name;
      self::$class[$name] = new $class_name();
    }
    return self::$class[$name];
  }

  //ファイルパス取得
  private static function GetPath($name) {
    return sprintf(self::PATH, JINROU_INC, $name);
  }
}

//-- オプションパーサ --//
class OptionParser {
  public $row;
  public $list = array();

  public function __construct($data) {
    $this->row  = $data;
    $this->list = self::Parse($this->row);
  }

  //取得
  static function Get($game_option, $option_role = '') {
    return array_merge(self::Parse($game_option), self::Parse($option_role));
  }

  //パース
  private static function Parse($data) {
    $list = array();
    foreach (explode(' ', $data) as $option) {
      if (empty($option)) continue;
      $stack = explode(':', $option);
      $list[$stack[0]] = count($stack) > 1 ? array_slice($stack, 1) : true;
    }
    return $list;
  }
}

//-- HTML 生成クラス (Option 拡張) --//
class OptionHTML {
  //ゲームオプション画像出力
  static function OutputImage($str) {
    $format = '<div class="game-option">%s%s%s</div>' . Text::LF;
    printf($format, OptionMessage::GAME_OPTION, Message::COLON, $str);
  }

  //村用オプション説明メッセージ生成
  static function GenerateRoomCaption($image, $url, $caption, $explain) {
    $format  = '<div>%s%s<a href="info/%s">%s</a>%s%s</div>' . Text::LF;
    return sprintf($format, $image, Message::COLON, $url, $caption, Message::COLON, $explain);
  }
}
