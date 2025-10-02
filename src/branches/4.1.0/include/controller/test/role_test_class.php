<?php
//-- オプション配役テストコントローラー --//
final class RoleTestController extends JinrouController {
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
      'normal', 'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'duel', 'duel_auto_open_cast',
      'duel_not_open_cast', 'gray_random', 'step', 'quiz'
    ];
    RQ::Get()->ParsePostData($id);
    $checked_key = in_array(RQ::Get()->$id, $stack) ? RQ::Get()->$id : 'chaos_hyper';
    foreach ($stack as $option) {
      $label   = $id . '_' . $option;
      $checked = HTML::GenerateChecked($checked_key == $option);
      DevHTML::OutputRadio($label, $id, $option, $checked, RoleTestMessage::$$option);
    }
    Text::d();

    foreach (['replace_human', 'change_common', 'change_mad', 'change_cupid'] as $option) {
      $count = 0;
      RQ::Get()->ParsePostData($option);
      foreach (GameOptionConfig::${$option.'_selector_list'} as $key => $mode) {
	Text::OutputFold(++$count, Text::BR, 11);
	if (is_int($key)) {
	  $value   = $mode;
	  $checked = HTML::GenerateChecked(RQ::Get()->$option == $mode);
	  $name    = OptionManager::GenerateCaption($mode);
	} else {
	  $value   = '';
	  $checked = HTML::GenerateChecked(RQ::Get()->$option == '');
	  $name    = $mode;
	}
	$label = $option . (is_int($key) ? '_' . $key : '');
	DevHTML::OutputRadio($label, $option, $value, $checked, $name);
      }
      Text::d();
    }

    foreach (['topping', 'boost_rate'] as $option) {
      $count = -1;
      RQ::Get()->ParsePostData($option);
      foreach (GameOptionConfig::${$option.'_list'} as $key => $mode) {
	Text::OutputFold(++$count, Text::BR, 9);
	$label   = $option . (is_int($key) ? '_' . $key : '');
	$checked = HTML::GenerateChecked(RQ::Get()->$option == $key);
	DevHTML::OutputRadio($label, $option, $key, $checked, $mode);
      }
      Text::d();
    }

    $id = 'open_cast';
    $stack = ['chaos_open_cast_camp', 'chaos_open_cast_role', 'chaos_open_cast_full'];
    RQ::Get()->ParsePostData($id);
    $checked_key = in_array(RQ::Get()->$id, $stack) ? RQ::Get()->$id : 'chaos_open_cast_full';
    foreach ($stack as $key => $option) {
      $label   = $id . '_' . $option;
      $checked = HTML::GenerateChecked($checked_key == $option);
      DevHTML::OutputRadio($label, $id, $option, $checked, RoleTestMessage::$$option);
    }
    Text::d();

    $stack = [
      'gerd', 'disable_gerd', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf',
      'tongue_wolf', 'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox',
      'depraver', 'cupid', 'medium', 'mania', 'detective', 'festival', 'limit_off'
    ];

    $count = 0;
    foreach ($stack as $option) {
      Text::OutputFold(++$count, Text::BR, 14);
      RQ::Get()->ParsePostData($option);
      $checked = Switcher::IsOn(RQ::Get()->$option);
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

    switch (RQ::Get()->game_option) { //メインオプション
    case 'chaos':
    case 'chaosfull':
    case 'chaos_hyper':
    case 'chaos_verso':
    case 'duel':
    case 'gray_random':
    case 'step':
    case 'quiz':
      $stack->game_option[] = RQ::Get()->game_option;
      break;

    case 'duel_auto_open_cast':
      $stack->game_option[] = 'duel';
      $stack->option_role[] = 'auto_open_cast';
      break;

    case 'duel_not_open_cast':
      $stack->game_option[] = 'duel';
      $stack->option_role[] = 'not_open_cast';
      break;
    }

    //置換系
    foreach (['replace_human', 'change_common', 'change_mad', 'change_cupid'] as $option) {
      RQ::Get()->ParsePostData($option);
      if (empty(RQ::Get()->$option)) {
	continue;
      }

      $list = $option . '_selector_list';
      if (array_search(RQ::Get()->$option, GameOptionConfig::$$list) !== false) {
	$stack->option_role[] = RQ::Get()->$option;
      }
    }

    //闇鍋用オプション
    foreach (['topping', 'boost_rate'] as $option) {
      RQ::Get()->ParsePostData($option);
      if (empty(RQ::Get()->$option)) {
	continue;
      }

      if (array_key_exists(RQ::Get()->$option, GameOptionConfig::${$option.'_list'})) {
	$stack->option_role[] = $option . ':' . RQ::Get()->$option;
      }
    }

    //陣営通知オプション
    switch (RQ::Get()->open_cast) {
    case 'chaos_open_cast_camp':
    case 'chaos_open_cast_role':
    case 'chaos_open_cast_full':
      $stack->option_role[] = RQ::Get()->open_cast;
      break;
    }

    //普通村向けオプション
    $option_stack = [
      'gerd', 'disable_gerd', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf',
      'tongue_wolf', 'possessed_wolf', 'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox',
      'depraver', 'cupid', 'medium', 'mania', 'detective'
    ];
    foreach ($option_stack as $option) {
      RQ::Get()->ParsePostOn($option);
      if (RQ::Get()->$option) {
	$stack->option_role[] = $option;
      }
    }

    foreach (['festival'] as $option) { //特殊村
      RQ::Get()->ParsePostOn($option);
      if (RQ::Get()->$option) {
	$stack->game_option[] = $option;
      }
    }

    RQ::Get()->ParsePostOn('limit_off');
    if (RQ::Get()->limit_off) {
      ChaosConfig::$role_group_rate_list = [];
    }
    DevRoom::Cast($stack);
  }
}
