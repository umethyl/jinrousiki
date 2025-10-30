<?php
//-- オプション配役テストコントローラー --//
final class RoleTestController extends JinrouAdminController {
  protected static function GetAdminType() {
    return 'role_test';
  }

  protected static function Output() {
    DevHTML::OutputRoleTestHeader(RoleTestMessage::TITLE, 'role_test.php');
    self::OutputForm();
    if (DevHTML::IsExecute()) {
      self::RunTest();
    }
    HTML::OutputFooter(true);
  }

  //フォーム出力
  private static function OutputForm() {
    $id    = 'game_option';
    $stack = [
      'normal', 'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'duel', 'gray_random',
      'step', 'quiz'
    ];
    RQ::Fetch()->ParsePostData($id);
    $checked_key = in_array(RQ::Fetch()->$id, $stack) ? RQ::Fetch()->$id : 'chaos_hyper';
    foreach ($stack as $option) {
      $label   = $id . '_' . $option;
      $checked = HTML::GenerateChecked($checked_key == $option);
      DevHTML::OutputRadio($label, $id, $option, $checked, RoleTestMessage::$$option);
    }
    Text::d();

    foreach (['replace_human', 'change_common', 'change_mad', 'change_cupid'] as $option) {
      $count = 0;
      RQ::Fetch()->ParsePostData($option);
      foreach (GameOptionConfig::${$option.'_selector_list'} as $key => $mode) {
	Text::OutputFold(++$count, Text::BR, 11);
	if (is_int($key)) {
	  $value   = $mode;
	  $checked = HTML::GenerateChecked(RQ::Fetch()->$option == $mode);
	  $name    = OptionManager::GenerateCaption($mode);
	} else {
	  $value   = '';
	  $checked = HTML::GenerateChecked(RQ::Fetch()->$option == '');
	  $name    = $mode;
	}
	$label = $option . (is_int($key) ? '_' . $key : '');
	DevHTML::OutputRadio($label, $option, $value, $checked, $name);
      }
      Text::d();
    }

    foreach (['topping', 'boost_rate', 'duel_selector'] as $option) {
      $count = -1;
      RQ::Fetch()->ParsePostData($option);
      foreach (GameOptionConfig::${$option.'_list'} as $key => $mode) {
	Text::OutputFold(++$count, Text::BR, 9);
	$label   = $option . (is_int($key) ? '_' . $key : '');
	$checked = HTML::GenerateChecked(RQ::Fetch()->$option == $key);
	DevHTML::OutputRadio($label, $option, $key, $checked, $mode);
      }
      Text::d();
    }

    foreach (['museum_topping'] as $option) {
      RQ::Fetch()->ParsePostData($option);
      if (RQ::Fetch()->$option) {
	RQ::Fetch()->ParsePostStr($option . '_input');
	$value = strtolower(RQ::Fetch()->{$option . '_input'});
      } else {
	$value = '';
      }
      $checked = Switcher::IsOn(RQ::Fetch()->$option);
      DevHTML::OutputCheckbox('option_' . $option, $option, RoleTestMessage::$$option, $checked);
      DevHTML::OutputText('option_' . $option . '_input', $option . '_input', $value);
    }
    Text::d();

    $id = 'open_cast';
    $stack = ['chaos_open_cast_camp', 'chaos_open_cast_role', 'chaos_open_cast_full'];
    RQ::Fetch()->ParsePostData($id);
    $checked_key = in_array(RQ::Fetch()->$id, $stack) ? RQ::Fetch()->$id : 'chaos_open_cast_full';
    foreach ($stack as $key => $option) {
      $label   = $id . '_' . $option;
      $checked = HTML::GenerateChecked($checked_key == $option);
      DevHTML::OutputRadio($label, $id, $option, $checked, RoleTestMessage::$$option);
    }
    Text::d();

    $stack = [
      'gerd', 'disable_gerd', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf',
      'tongue_wolf', 'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox',
      'depraver', 'cupid', 'medium', 'mania', 'detective', 'festival', 'limit_off', 'increment'
    ];

    $count = 0;
    foreach ($stack as $option) {
      Text::OutputFold(++$count, Text::BR, 14);
      RQ::Fetch()->ParsePostData($option);
      $checked = Switcher::IsOn(RQ::Fetch()->$option);
      DevHTML::OutputCheckbox('option_' . $option, $option, RoleTestMessage::$$option, $checked);
    }
    HTML::OutputFormFooter();
  }

  //テスト実行
  private static function RunTest() {
    RQ::InitTestRoom();
    $stack = new stdClass();
    $stack->game_option = ['dummy_boy'];
    $stack->option_role = [];

    switch (RQ::Fetch()->game_option) { //メインオプション
    case 'chaos':
    case 'chaosfull':
    case 'chaos_hyper':
    case 'chaos_verso':
    case 'duel':
    case 'gray_random':
    case 'step':
    case 'quiz':
      $stack->game_option[] = RQ::Fetch()->game_option;
      break;
    }

    //置換系
    foreach (['replace_human', 'change_common', 'change_mad', 'change_cupid'] as $option) {
      RQ::Fetch()->ParsePostData($option);
      if (empty(RQ::Fetch()->$option)) {
	continue;
      }

      $list = $option . '_selector_list';
      if (array_search(RQ::Fetch()->$option, GameOptionConfig::$$list) !== false) {
	$stack->option_role[] = RQ::Fetch()->$option;
      }
    }

    //闇鍋/決闘用オプション
    foreach (['topping', 'boost_rate', 'duel_selector'] as $option) {
      RQ::Fetch()->ParsePostData($option);
      if (empty(RQ::Fetch()->$option)) {
	continue;
      }

      if (array_key_exists(RQ::Fetch()->$option, GameOptionConfig::${$option.'_list'})) {
	$stack->option_role[] = $option . ':' . RQ::Fetch()->$option;
      }
    }

    //闇鍋用オプション(テキスト型)
    foreach (['museum_topping'] as $option) {
      RQ::Fetch()->ParsePostData($option);
      if (empty(RQ::Fetch()->$option)) {
	continue;
      }

      $input = $option . '_input';
      RQ::Fetch()->ParsePostStr($input);
      if (empty(RQ::Fetch()->$input)) {
	continue;
      }
      RQ::Fetch()->$input = strtolower(RQ::Fetch()->$input);
      if (array_key_exists(RQ::Fetch()->$input, ChaosConfig::${$option.'_list'})) {
	$stack->option_role[] = $option . ':' . RQ::Fetch()->$input;
      }
    }

    //陣営通知オプション
    switch (RQ::Fetch()->open_cast) {
    case 'chaos_open_cast_camp':
    case 'chaos_open_cast_role':
    case 'chaos_open_cast_full':
      $stack->option_role[] = RQ::Fetch()->open_cast;
      break;
    }

    //普通村向けオプション
    $option_stack = [
      'gerd', 'disable_gerd', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf',
      'tongue_wolf', 'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox',
      'depraver', 'cupid', 'medium', 'mania', 'detective'
    ];
    foreach ($option_stack as $option) {
      RQ::Fetch()->ParsePostOn($option);
      if (RQ::Fetch()->$option) {
	$stack->option_role[] = $option;
      }
    }

    foreach (['festival'] as $option) { //特殊村
      RQ::Fetch()->ParsePostOn($option);
      if (RQ::Fetch()->$option) {
	$stack->game_option[] = $option;
      }
    }

    RQ::Fetch()->ParsePostOn('limit_off');
    if (RQ::Fetch()->limit_off) {
      ChaosConfig::$role_group_rate_list = [];
    }
    DevRoom::Cast($stack);
  }
}
