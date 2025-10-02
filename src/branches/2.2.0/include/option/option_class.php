<?php
//-- オプションマネージャ --//
class OptionManager {
  const PATH = '%s/option/%s.php';
  public  static $stack;
  public  static $change = false;
  private static $file  = array();
  private static $class = array();

  //特殊普通村編成リスト
  private static $role_list = array(
    'detective', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania');

  //特殊サブ配役リスト
  private static $cast_list = array(
    'decide', 'authority', 'joker', 'deep_sleep', 'blinder', 'mind_open',
    'perverseness', 'liar', 'gentleman', 'passion', 'critical', 'sudden_death', 'quiz');

  //ファイルロード
  static function Load($name) {
    if (is_null($name) || ! file_exists($file = self::GetPath($name))) return false;
    if (in_array($name, self::$file)) return true;
    require_once($file);
    self::$file[] = $name;
    return true;
  }

  //クラス取得
  static function GetClass($name) {
    return self::Load($name) ? self::LoadClass($name) : null;
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
  static function Cast(array &$list, &$rand) {
    $delete = self::$stack;
    foreach (self::$cast_list as $option) {
      if (DB::$ROOM->IsOption($option) && self::Load($option)) {
	$stack = self::LoadClass($option)->Cast($list, $rand);
	if (is_array($stack)) $delete = array_merge($delete, $stack);
      }
    }
    self::$stack = $delete;
  }

  //役職置換処理
  static function Replace(array &$list, $base, $target) {
    if (! isset($list[$base]) || $list[$base] < 1) return false;
    $list[$base]--;
    isset($list[$target]) ? $list[$target]++ : $list[$target] = 1;
    return true;
  }

  //オプション名生成
  static function GenerateCaption($name) {
    return self::Load($name) ? self::LoadClass($name)->GetName() : '';
  }

  //オプション名出力
  static function OutputCaption($name) { echo self::GenerateCaption($name); }

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
  private static function GetPath($name) { return sprintf(self::PATH, JINRO_INC, $name); }
}

//-- オプションパーサ --//
class OptionParser {
  public $row;
  public $list = array();

  function __construct($data) {
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
