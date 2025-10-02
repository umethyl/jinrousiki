<?php
//-- 村作成オプションクラス --//
class RoomOption {
  const NOT_OPTION  = '';
  const GAME_OPTION = 'game_option';
  const ROLE_OPTION = 'role_option';

  static $stack       = array();
  static $game_option = array();
  static $role_option = array();
  static $icon_order  = array(
    'wish_role', 'real_time', 'dummy_boy', 'gm_login', 'gerd', 'wait_morning', 'open_vote',
    'settle', 'seal_message', 'open_day', 'necessary_name', 'necessary_trip', 'not_open_cast',
    'auto_open_cast', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania', 'decide',
    'authority', 'detective', 'liar', 'gentleman', 'passion', 'deep_sleep', 'blinder', 'mind_open',
    'sudden_death', 'perverseness', 'critical', 'joker', 'death_note', 'weather', 'festival',
    'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire', 'full_chiroptera',
    'full_mania', 'full_unknown_mania', 'change_common', 'change_hermit_common', 'change_mad',
    'change_fanatic_mad', 'change_whisper_mad', 'change_immolate_mad', 'change_cupid',
    'change_mind_cupid', 'change_triangle_cupid', 'change_angel', 'duel', 'gray_random', 'step',
    'quiz', 'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'topping', 'boost_rate',
    'chaos_open_cast', 'chaos_open_cast_camp', 'chaos_open_cast_role', 'secret_sub_role',
    'no_sub_role', 'sub_role_limit_easy', 'sub_role_limit_normal', 'sub_role_limit_hard');
  static $max_user = 0;

  //オプション情報ロード
  static function Load(array $list = array()) {
    if (count($list) < 1) $list = RoomDB::GetOption();
    extract($list);
    self::$stack       = array();
    self::$game_option = $game_option;
    self::$role_option = $option_role;
    self::$max_user    = $max_user;
  }

  //フォーム入力値取得
  static function LoadPost($name) {
    foreach (func_get_args() as $option) {
      $filter = OptionManager::GetClass($option);
      if (isset($filter)) $filter->LoadPost();
    }
  }

  //登録オプション取得
  static function Get($type) { return implode(' ', self::$$type); }

  //スタックから表示順に取得
  static function GetOrder() {
    if (count(self::$stack) < 1) self::SetStack();
    return array_intersect(self::$icon_order, array_keys(self::$stack));
  }

  //オプション登録
  static function Set($type, $name) {
    RQ::Set($name, true);
    if (! in_array($name, self::$$type)) array_push(self::$$type, $name);
  }

  //オプションをパースしてスタック登録
  static function SetStack() {
    self::$stack = OptionParser::Get(self::$game_option, self::$role_option);
  }

  //ゲームオプション情報生成
  static function Generate() {
    return self::GenerateImage() . Image::GenerateMaxUser(self::$max_user);
  }

  //ゲームオプション画像生成
  static function GenerateImage() {
    $str = '';
    foreach (self::GetOrder() as $option) {
      $str .= OptionManager::GetClass($option)->GenerateImage();
    }
    return $str;
  }

  //ゲームオプション画像出力
  static function Output() {
    self::Load();
    $format = '<div class="game-option">ゲームオプション：%s</div>' . Text::LF;
    printf($format, self::Generate());
  }

  //ゲームオプション説明生成
  static function OutputCaption() {
    $str   = '';
    foreach (self::GetOrder() as $option) {
      $str .= OptionManager::GetClass($option)->GenerateRoomCaption();
    }
    echo $str;
  }
}
