<?php
//-- オプション入力画面表示クラス --//
class OptionForm {
  private static $order = array(
    'room_name', 'room_comment', 'max_user',
    'base' => null,
    'wish_role', 'real_time', 'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name',
    'necessary_trip',
    'dummy_boy' => null,
    'dummy_boy_selector', 'gm_password', 'gerd',
    'talk' => null,
    'wait_morning', 'limit_talk', 'secret_talk', 'no_silence',
    'open_cast' => null,
    'not_open_cast_selector',
    'add_role' => null,
    'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
    'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'cupid', 'medium', 'mania',
    'decide', 'authority',
    'special' => null,
    'liar', 'gentleman', 'passion', 'sudden_death', 'perverseness', 'deep_sleep', 'mind_open',
    'blinder', 'critical', 'joker', 'death_note', 'detective', 'weather', 'full_weather',
    'festival', 'replace_human_selector', 'change_common_selector', 'change_mad_selector',
    'change_cupid_selector',
    'special_cast' => null,
    'special_role',
    'chaos' => null,
    'topping', 'boost_rate', 'chaos_open_cast', 'sub_role_limit', 'secret_sub_role'
  );

  private static $javascript = array();

  //出力
  static function Output() {
    $class = '';
    foreach (self::$order as $group => $name) {
      if (! is_int($group)) $class = sprintf(' class="%s"', $group); //class 切り替え
      is_null($name) ? self::OutputSeparator($group) : self::OutputForm($name, $class);
    }

    if (count(self::$javascript) > 0) OptionFormHTML::OutputJavaScript(self::$javascript);
  }

  //フォーム出力 (振り分け処理用)
  private static function OutputForm($name, $class) {
    $filter = OptionManager::GetClass($name);
    if (! $filter->enable || ! isset($filter->type)) return;

    switch ($filter->type) {
    case 'textbox':
    case 'password':
      $str = self::GenerateTextbox($filter);
      break;

    case 'checkbox':
    case 'radio':
      $str = self::GenerateCheckbox($filter);
      break;

    case 'realtime':
      $str = self::GenerateRealtime($filter);
      break;

    case 'limit_talk':
      $str = self::GenerateLimitTalk($filter);
      break;

    case 'selector':
      $str = self::GenerateSelector($filter);
      break;

    case 'group':
      $str = self::GenerateGroup($filter);
      break;
    }
    OptionFormHTML::OutputForm($filter, $class, $str);
  }

  //境界線出力
  private static function OutputSeparator($group) {
    OptionFormHTML::OutputSeparator();
    if (OptionManager::IsChange()) return;

    switch ($group) {
    case 'base':
    case 'dummy_boy':
    case 'talk':
      $flag = 'false';
      break;

    case 'open_cast':
    case 'add_role':
    case 'special':
      $flag = 'true';
      break;

    default:
      return;
    }
    self::$javascript[] = sprintf("toggle_option_display('%s', %s);", $group, $flag);
    OptionFormHTML::OutputToggle($group, OptionMessage::${'category_' . $group});
  }

  //テキストボックス生成
  private static function GenerateTextbox(TextRoomOptionItem $filter) {
    return OptionFormHTML::GenerateTextbox($filter);
  }

  //チェックボックス生成
  private static function GenerateCheckbox(CheckRoomOptionItem $filter) {
    $footer = isset($filter->footer) ? $filter->footer : sprintf('(%s)', $filter->GetExplain());
    return OptionFormHTML::GenerateCheckbox($filter, $filter->type, Text::Line($footer));
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

    return OptionFormHTML::GenerateCheckbox($filter, 'checkbox', $footer);
  }

  //チェックボックス生成 (発言制限制専用)
  private static function GenerateLimitTalk(Option_limit_talk $filter) {
    if (OptionManager::IsChange() && DB::$ROOM->IsOption($filter->name)) {
      $count = array_shift(DB::$ROOM->game_option->list[$filter->name]);
    } else {
      $count = GameConfig::LIMIT_TALK_COUNT;
    }
    $footer = OptionFormHTML::GenerateLimitTalk($filter, $count);

    return OptionFormHTML::GenerateCheckbox($filter, 'checkbox', $footer);
  }

  //セレクタ生成
  private static function GenerateSelector(SelectorRoomOptionItem $filter) {
    $str = '';
    foreach ($filter->GetItem() as $code => $child) {
      $label = $child instanceof RoomOptionItem ? $child->GetCaption() : $child;
      if (! is_string($code)) $code = $label;
      $selected = $code == $filter->value ? ' selected' : '';
      $str .= OptionFormHTML::GenerateSelectorOption($code, $selected, $label);
    }

    if (! OptionManager::IsChange() && isset($filter->javascript)) {
      self::$javascript[] = $filter->javascript;
    }

    return OptionFormHTML::GenerateSelector($filter, $str);
  }

  //グループ生成
  private static function GenerateGroup(RoomOptionItem $filter) {
    $str = '';
    foreach ($filter->GetItem() as $child) {
      if (empty($child->type)) continue;
      switch ($child->type) {
      case 'radio':
	$str .= self::GenerateCheckbox($child);
	break;
      }
      $str .= Text::BRLF;
    }

    return $str;
  }
}

//-- HTML 生成クラス (OptionFrom 拡張) --//
class OptionFormHTML {
  //村作成オプションフォーム出力
  static function OutputForm(RoomOptionItem $filter, $class, $str) {
    $format = <<<EOF
<tr%s>
  <td class="title"><label for="%s">%s%s</label></td>
  <td>%s</td>
</tr>
EOF;
    printf($format . Text::LF,
	   $class, $filter->name, $filter->GetCaption(), Message::COLON, $str);
  }

  //境界線出力
  static function OutputSeparator() {
    Text::Output('<tr><td colspan="2"><hr></td></tr>');
  }

  //表示制御リンク出力
  static function OutputToggle($group, $name) {
    $format = <<<EOF
<tr class="%s" id="%s_on">
  <td class="title"><label onClick="toggle_option_display('%s', true)">%s</label></td>
  <td onClick="toggle_option_display('%s', true)"><a href="javascript:void(0)">%s</a></td>
</tr>
<tr id="%s_off">
  <td class="title"><label onClick="toggle_option_display('%s', false)">%s</label></td>
  <td onClick="toggle_option_display('%s', false)"><a href="javascript:void(0)">%s</a></td>
</tr>
EOF;

    printf($format . Text::LF,
	   $group, $group, $group, $name, $group, OptionMessage::TOGGLE_OFF,
	   $group, $group, $name, $group, OptionMessage::TOGGLE_ON);
  }

  //テキストボックス生成
  static function GenerateTextbox(TextRoomOptionItem $filter) {
    $format = '<input type="%s" name="%s" id="%s" size="%d" value="%s">%s';
    $size   = sprintf('%s_input', $filter->name);
    $str    = $filter->GetExplain();
    if (OptionManager::IsChange()) {
      $value = DB::$ROOM->{array_pop(explode('_', $filter->name))};
    } else {
      $value = null;
    }

    return sprintf($format,
		   $filter->type, $filter->name, $filter->name, RoomConfig::$$size, $value,
		   isset($str) ? sprintf(' <span class="explain">%s</span>', $str) : '');
  }

  //チェックボックス生成
  static function GenerateCheckbox(CheckRoomOptionItem $filter, $type, $footer) {
    $format = '<input type="%s" id="%s" name="%s" value="%s"%s> <span class="explain">%s</span>';
    return sprintf($format,
		   $type, $filter->name, $filter->form_name, $filter->form_value,
		   $filter->value ? ' checked' : '', $footer);
  }

  //時刻入力フォーム生成 (リアルタイム制用)
  static function GenerateRealtime(Option_real_time $filter, $day, $night) {
    $format = '(%s%s' .
      '%s%s<input type="text" name="%s_day" value="%d" size="2" maxlength="2">%s ' .
      '%s%s<input type="text" name="%s_night" value="%d" size="2" maxlength="2">%s)';

    return sprintf($format,
		   Text::Line($filter->GetExplain()), Message::SPACER,
		   OptionMessage::REALTIME_DAY, Message::COLON,
		   $filter->name, $day, Message::MINUTE,
		   OptionMessage::REALTIME_NIGHT, Message::COLON,
		   $filter->name, $night, Message::MINUTE);
  }

  //発言数フォーム生成 (発言数制限制用)
  static function GenerateLimitTalk(Option_limit_talk $filter, $count) {
    $format = '(%s%s<input type="text" name="%s" value="%d" size="2" maxlength="2">)';

    return sprintf($format,
		   Text::Line($filter->GetExplain()), Message::SPACER,
		   $filter->name . '_count', $count);
  }

  //セレクタ生成
  static function GenerateSelector(SelectorRoomOptionItem $filter, $str) {
    $format = <<<EOF
<select id="%s" name="%s"%s>
<optgroup label="%s">
%s</optgroup>
</select>
<span class="explain">(%s)</span>
EOF;
    return sprintf($format,
		   $filter->name, $filter->form_name, $filter->on_change, $filter->label,
		   $str, Text::Line($filter->GetExplain()));
  }

  //セレクタ個別項目生成
  static function GenerateSelectorOption($code, $selected, $label) {
    $format = '  <option value="%s"%s>%s</option>';
    return sprintf($format . Text::LF, $code, $selected, $label);
  }

  //JavaScript 出力
  static function OutputJavaScript(array $list) {
    echo HTML::GenerateJavaScriptHeader();
    foreach ($list as $code) Text::Output($code);
    echo HTML::GenerateJavaScriptFooter();
  }
}
