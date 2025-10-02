<?php
//-- 村作成オプションクラス --//
class RoomOption {
  const NOT_OPTION  = '';
  const GAME_OPTION = 'game_option';
  const ROLE_OPTION = 'role_option';

  static $game_option = array();
  static $role_option = array();
  static $icon_order  = array(
    'wish_role', 'real_time', 'dummy_boy', 'gm_login', 'gerd', 'wait_morning', 'open_vote',
    'settle', 'seal_message', 'open_day', 'necessary_name', 'necessary_trip', 'not_open_cast',
    'auto_open_cast', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf',
    'possessed_wolf', 'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania', 'decide',
    'authority', 'detective', 'liar', 'gentleman', 'deep_sleep', 'blinder', 'mind_open',
    'sudden_death', 'perverseness', 'critical', 'joker', 'death_note', 'weather', 'festival',
    'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire', 'full_chiroptera',
    'full_mania', 'full_unknown_mania', 'change_common', 'change_hermit_common', 'change_mad',
    'change_fanatic_mad', 'change_whisper_mad', 'change_immolate_mad', 'change_cupid',
    'change_mind_cupid', 'change_triangle_cupid', 'change_angel', 'duel', 'gray_random', 'quiz',
    'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'topping', 'boost_rate', 'chaos_open_cast',
    'chaos_open_cast_camp', 'chaos_open_cast_role', 'secret_sub_role', 'no_sub_role',
    'sub_role_limit_easy', 'sub_role_limit_normal', 'sub_role_limit_hard');

  //登録されたオプションを取得
  static function GetOption($type) { return implode(' ', self::$$type); }

  //オプションを登録
  static function SetOption($type, $name) {
    RQ::$get->$name = true;
    if (! in_array($name, self::$$type)) array_push(self::$$type, $name);
  }

  //フォームからの入力値を取得
  static function LoadPost($name) {
    foreach (func_get_args() as $option) {
      $filter = OptionManager::GetClass($option);
      if (isset($filter)) $filter->LoadPost();
    }
  }

  //ゲームオプション情報生成
  static function Generate($game_option, $option_role, $max_user) {
    return self::GenerateImage($game_option, $option_role) . Image::GenerateMaxUser($max_user);
  }

  //ゲームオプション画像生成
  static function GenerateImage($game_option, $option_role = '') {
    //オプションパース
    $list = array_merge(OptionParser::Parse($game_option), OptionParser::Parse($option_role));

    $str = '';
    foreach (array_intersect(self::$icon_order, array_keys($list)) as $option) {
      $filter   = OptionManager::GetClass($option);
      $sentence = $filter->GetCaption();
      if (isset(CastConfig::$option) && is_int(CastConfig::$$option)) {
	$sentence .= sprintf('(%d人～)', CastConfig::$$option);
      }
      switch ($option) {
      case 'real_time':
        list($day, $night) = $list[$option];
	$footer = sprintf('[%d：%d]', $day, $night);
        $sentence .= sprintf('　昼： %d 分　夜： %d 分', $day, $night);
	break;

      case 'topping':
      case 'boost_rate':
	$type   = $list[$option][0];
	$item   = $filter->GetItem();
	$footer = sprintf('[%s]', strtoupper($type));
	$sentence .= sprintf('(Type%s)', $item[$type]);
	break;

      default:
	$footer = '';
	break;
      }
      $str .= Image::Room()->Generate($option, $sentence) . $footer;
    }
    return $str;
  }

  //ゲームオプション画像出力
  static function Output() {
    $query = DB::$ROOM->GetQueryHeader('room', 'game_option', 'option_role', 'max_user');
    extract(DB::FetchAssoc($query, true));
    $format = "<div class=\"game-option\">ゲームオプション：%s</div>\n";
    printf($format, self::Generate($game_option, $option_role, $max_user));
  }
}
