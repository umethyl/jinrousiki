<?php
//-- 村作成オプションクラス --//
class RoomOption {
  public static $stack       = [];
  public static $game_option = [];
  public static $role_option = [];
  public static $icon_order  = [
    'wish_role', 'real_time', 'dummy_boy', 'gm_login', 'temporary_gm', 'gerd', 'disable_gerd',
    'dummy_boy_cast_limit', 'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name',
    'necessary_trip', 'wait_morning', 'limit_last_words', 'limit_talk', 'secret_talk', 'no_silence',
    'not_open_cast', 'auto_open_cast',
    'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
    'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid', 'medium', 'mania',
    'decide', 'authority', 'detective', 'liar', 'gentleman', 'passion', 'deep_sleep', 'blinder',
    'mind_open', 'sudden_death', 'perverseness', 'critical', 'notice_critical', 'joker',
    'death_note', 'weather', 'full_weather', 'festival',
    'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire', 'full_chiroptera',
    'full_patron', 'full_mania', 'full_unknown_mania',
    'change_common', 'change_hermit_common', 'change_mad', 'change_fanatic_mad',
    'change_whisper_mad', 'change_immolate_mad', 'change_cupid', 'change_mind_cupid',
    'change_triangle_cupid', 'change_angel', 'change_exchange_angel',
    'duel', 'duel_selector', 'gray_random', 'step', 'quiz',
    'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'topping', 'boost_rate',
    'chaos_open_cast', 'chaos_open_cast_camp', 'chaos_open_cast_role', 'secret_sub_role',
    'no_sub_role', 'sub_role_limit_easy', 'sub_role_limit_normal', 'sub_role_limit_hard'
  ];
  public static $max_user = 0;

  //オプション情報ロード
  public static function Load(array $list = []) {
    if (count($list) < 1) {
      $list = RoomDB::GetOption();
    }
    extract($list);
    self::$stack       = [];
    self::$game_option = $game_option;
    self::$role_option = $option_role;
    self::$max_user    = $max_user;
  }

  //フォーム入力値取得
  public static function LoadPost(...$option_list) {
    foreach ($option_list as $option) {
      $filter = OptionLoader::Load($option);
      if (true === isset($filter)) {
	$filter->LoadPost();
      }
    }
  }

  //登録オプション取得
  public static function Get($type) {
    return ArrayFilter::Concat(self::$$type);
  }

  //スタックから表示順に取得
  public static function GetOrder() {
    if (count(self::$stack) < 1) {
      self::SetStack();
    }
    return array_intersect(self::$icon_order, array_keys(self::$stack));
  }

  //オプション登録
  public static function Set($type, $name) {
    RQ::Set($name, true);
    if (false === in_array($name, self::$$type)) {
      array_push(self::$$type, $name);
    }
  }

  //オプションをパースしてスタック登録
  public static function SetStack() {
    self::$stack = OptionParser::Get(self::$game_option, self::$role_option);
  }

  //ゲームオプション情報生成
  public static function Generate() {
    return self::GenerateImage() . ImageManager::Room()->GenerateMaxUser(self::$max_user);
  }

  //ゲームオプション画像生成
  public static function GenerateImage() {
    $str = '';
    foreach (self::GetOrder() as $option) {
      $str .= OptionLoader::Load($option)->GenerateImage();
    }
    return $str;
  }

  //ゲームオプション画像出力
  public static function Output() {
    self::Load();
    OptionHTML::OutputImage(self::Generate());
  }

  //ゲームオプション説明生成
  public static function OutputCaption() {
    $str = '';
    foreach (self::GetOrder() as $option) {
      $str .= OptionLoader::Load($option)->GenerateRoomCaption();
    }
    echo $str;
  }
}
