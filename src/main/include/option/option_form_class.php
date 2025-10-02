<?php
//-- オプション入力画面表示クラス --//
class OptionForm {
  private static $order = [
    'room_name', 'room_comment', 'max_user',
    'base' => null,
    'wish_role', 'real_time', 'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name',
    'necessary_trip', 'close_room',
    'dummy_boy' => null,
    'dummy_boy_selector', 'gm_password', 'gerd',
    'talk' => null,
    'wait_morning', 'limit_last_words', 'limit_talk', 'secret_talk', 'no_silence',
    'open_cast' => null,
    'not_open_cast_selector',
    'add_role' => null,
    'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
    'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid', 'medium', 'mania',
    'decide', 'authority',
    'special' => null,
    'liar', 'gentleman', 'passion', 'sudden_death', 'perverseness', 'deep_sleep', 'mind_open',
    'blinder', 'critical', 'notice_critical', 'joker', 'death_note', 'detective', 'weather',
    'full_weather', 'festival', 'replace_human_selector', 'change_common_selector',
    'change_mad_selector', 'change_cupid_selector',
    'special_cast' => null,
    'special_role',
    'chaos' => null,
    'topping', 'boost_rate', 'chaos_open_cast', 'sub_role_limit', 'secret_sub_role'
  ];

  private static $javascript = [];

  //出力
  public static function Output() {
    $class = '';
    foreach (self::$order as $group => $name) {
      if (false === is_int($group)) {
	$class = sprintf(' class="%s"', $group); //class 切り替え
      }
      is_null($name) ? self::OutputSeparator($group) : self::OutputForm($name, $class);
    }

    if (count(self::$javascript) > 0) {
      OptionFormHTML::OutputJavaScript(self::$javascript);
    }
  }

  //フォーム出力 (振り分け処理用)
  private static function OutputForm($name, $class) {
    $filter = OptionLoader::Load($name);
    if (! $filter->enable || false === isset($filter->type)) {
      return;
    }

    switch ($filter->type) {
    case OptionFormType::TEXT:
    case OptionFormType::PASSWORD:
      $str = self::GenerateTextbox($filter);
      break;

    case OptionFormType::CHECKBOX:
    case OptionFormType::RADIO:
      $str = self::GenerateCheckbox($filter);
      break;

    case OptionFormType::REALTIME:
      $str = self::GenerateRealtime($filter);
      break;

    case OptionFormType::LIMIT_TALK:
      $str = self::GenerateLimitTalk($filter);
      break;

    case OptionFormType::SELECTOR:
      $str = self::GenerateSelector($filter);
      break;

    case OptionFormType::GROUP:
      $str = self::GenerateGroup($filter);
      break;
    }
    OptionFormHTML::Output($filter, $class, $str);
  }

  //境界線出力
  private static function OutputSeparator($group) {
    OptionFormHTML::OutputSeparator();
    if (OptionManager::IsChange()) {
      return;
    }

    switch ($group) {
    case 'base':
    case 'dummy_boy':
    case 'talk':
      $flag = Switcher::NG;
      break;

    case 'open_cast':
    case 'add_role':
    case 'special':
      $flag = Switcher::OK;
      break;

    default:
      return;
    }
    self::$javascript[] = sprintf("toggle_option_display('%s', %s);", $group, $flag);
    OptionFormHTML::OutputToggle($group, OptionMessage::${'category_' . $group});
  }

  //テキストボックス生成
  private static function GenerateTextbox(OptionText $filter) {
    return OptionFormHTML::GenerateTextbox($filter);
  }

  //チェックボックス生成
  private static function GenerateCheckbox(OptionCheckbox $filter) {
    $footer = isset($filter->footer) ? $filter->footer : Text::Quote($filter->GetExplain());
    return OptionFormHTML::GenerateCheckbox($filter, $filter->type, Text::ConvertLine($footer));
  }

  //チェックボックス生成 (リアルタイム制専用)
  private static function GenerateRealtime(Option_real_time $filter) {
    if (OptionManager::IsChange()) {
      $day   = DB::$ROOM->game_option->list[$filter->name][0];
      $night = DB::$ROOM->game_option->list[$filter->name][1];
    } else {
      $day   = TimeConfig::DEFAULT_DAY;
      $night = TimeConfig::DEFAULT_NIGHT;
    }
    $footer = OptionFormHTML::GenerateRealtime($filter, $day, $night);

    return OptionFormHTML::GenerateCheckbox($filter, OptionFormType::CHECKBOX, $footer);
  }

  //チェックボックス生成 (発言制限制専用)
  private static function GenerateLimitTalk(Option_limit_talk $filter) {
    if (OptionManager::IsChange() && DB::$ROOM->IsOption($filter->name)) {
      $count = ArrayFilter::Pick(DB::$ROOM->game_option->list[$filter->name]);
    } else {
      $count = GameConfig::LIMIT_TALK_COUNT;
    }
    $footer = OptionFormHTML::GenerateLimitTalk($filter, $count);

    return OptionFormHTML::GenerateCheckbox($filter, OptionFormType::CHECKBOX, $footer);
  }

  //セレクタ生成
  private static function GenerateSelector(OptionSelector $filter) {
    $str = '';
    foreach ($filter->GetItem() as $code => $child) {
      $label = $child instanceof Option ? $child->GetCaption() : $child;
      if (! is_string($code)) {
	$code = $label;
      }
      $selected = HTML::GenerateSelected($code == $filter->value);
      $str .= OptionFormHTML::GenerateSelectorOption($code, $selected, $label);
    }

    if (! OptionManager::IsChange() && isset($filter->javascript)) {
      self::$javascript[] = $filter->javascript;
    }

    return OptionFormHTML::GenerateSelector($filter, $str);
  }

  //グループ生成
  private static function GenerateGroup(Option $filter) {
    $str = '';
    foreach ($filter->GetItem() as $child) {
      if (empty($child->type)) {
	continue;
      }

      switch ($child->type) {
      case OptionFormType::RADIO:
	$str .= self::GenerateCheckbox($child);
	break;
      }
      $str .= Text::BRLF;
    }

    return $str;
  }
}
