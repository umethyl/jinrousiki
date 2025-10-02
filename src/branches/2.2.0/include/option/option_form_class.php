<?php
//-- オプション入力画面表示クラス --//
class OptionForm {
  const TEXTBOX = '<input type="%s" name="%s" id="%s" size="%d" value="%s">%s';
  const TEXTBOX_EXPLAIN = ' <span class="explain">%s</span>';
  const CHECKBOX = '<input type="%s" id="%s" name="%s" value="%s"%s> <span class="explain">%s</span>';
  const REALTIME = '(%s　昼：<input type="text" name="%s_day" value="%d" size="2" maxlength="2">分 夜：<input type="text" name="%s_night" value="%d" size="2" maxlength="2">分)';
  const SELECTOR = "  <option value=\"%s\"%s>%s</option>\n";

  private static $order = array(
    'room_name', 'room_comment', 'max_user',
    'base' => null,
    'wish_role', 'real_time', 'wait_morning', 'open_vote', 'settle', 'seal_message', 'open_day',
    'necessary_name', 'necessary_trip',
    'dummy_boy' => null,
    'dummy_boy_selector', 'gm_password', 'gerd',
    'open_cast' => null,
    'not_open_cast_selector',
    'add_role' => null,
    'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
    'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania', 'decide', 'authority',
    'special' => null,
    'liar', 'gentleman', 'passion', 'sudden_death', 'perverseness', 'deep_sleep', 'mind_open',
    'blinder', 'critical', 'joker', 'death_note', 'detective', 'weather', 'festival',
    'replace_human_selector', 'change_common_selector', 'change_mad_selector',
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
      is_null($name) ? self::GenerateSeparator($group) : self::Generate($name, $class);
    }

    if (count(self::$javascript) > 0) {
      Text::Output("<script type=\"text/javascript\">\n<!--");
      foreach (self::$javascript as $code) Text::Output($code);
      Text::Output("//-->\n</script>");
    }
  }

  //生成 (振り分け処理用)
  private static function Generate($name, $class) {
    $item = OptionManager::GetClass($name);
    if (! $item->enable || ! isset($item->type)) return;

    switch ($item->type) {
    case 'textbox':
    case 'password':
      $str = self::GenerateTextbox($item);
      break;

    case 'checkbox':
    case 'radio':
      $str = self::GenerateCheckbox($item);
      break;

    case 'realtime':
      $str = self::GenerateRealtime($item);
      break;

    case 'selector':
      $str = self::GenerateSelector($item);
      break;

    case 'group':
      $str = self::GenerateGroup($item);
      break;
    }

    $format = <<<EOF
  <tr%s>
    <td class="title"><label for="%s">%s：</label></td>
    <td>%s</td>
  </tr>

EOF;
    printf($format, $class, $item->name, $item->GetCaption(), $str);
  }

  //境界線生成
  private static function GenerateSeparator($group) {
    Text::Output('  <tr><td colspan="2"><hr></td></tr>');
    if (OptionManager::$change) return;

    switch ($group) {
    case 'base':
      $name = '基本オプション';
      self::$javascript[] = sprintf("toggle_option_display('%s', false)", $group);
      break;

    case 'dummy_boy':
      $name = '身代わり君設定';
      self::$javascript[] = sprintf("toggle_option_display('%s', false)", $group);
      break;

    case 'open_cast':
      $name = '霊界公開設定';
      self::$javascript[] = sprintf("toggle_option_display('%s', true)", $group);
      break;

    case 'add_role':
      $name = '追加役職設定';
      self::$javascript[] = sprintf("toggle_option_display('%s', true)", $group);
      break;

    case 'special':
      $name = '特殊設定';
      self::$javascript[] = sprintf("toggle_option_display('%s', true)", $group);
      break;

    default:
      return;
    }

    $format = <<<EOF
   <tr class="%s" id="%s_on">
     <td class="title"><label onClick="toggle_option_display('%s', true)">%s</label></td>
     <td onClick="toggle_option_display('%s', true)"><a href="javascript:void(0)">折り畳む</a></td>
  </tr>

   <tr id="%s_off">
     <td class="title"><label onClick="toggle_option_display('%s', false)">%s</label></td>
     <td onClick="toggle_option_display('%s', false)"><a href="javascript:void(0)">展開する</a></td>
  </tr>

EOF;
    printf($format, $group, $group, $group, $name, $group,
	   $group, $group, $name, $group);
  }

  //テキストボックス生成
  private static function GenerateTextbox(TextRoomOptionItem $item) {
    $size  = sprintf('%s_input', $item->name);
    $str   = $item->GetExplain();
    $value = OptionManager::$change ? DB::$ROOM->{array_pop(explode('_', $item->name))} : null;

    return sprintf(self::TEXTBOX, $item->type, $item->name, $item->name, RoomConfig::$$size,
		   $value, isset($str) ? sprintf(self::TEXTBOX_EXPLAIN, $str) : '');
  }

  //チェックボックス生成
  private static function GenerateCheckbox(CheckRoomOptionItem $item) {
    $footer = isset($item->footer) ? $item->footer : sprintf('(%s)', $item->GetExplain());

    return sprintf(self::CHECKBOX, $item->type, $item->name, $item->form_name, $item->form_value,
		   $item->value ? ' checked' : '', Text::Line($footer));
  }

  //チェックボックス生成 (リアルタイム制専用)
  private static function GenerateRealtime(Option_real_time $item) {
    if (OptionManager::$change) {
      $day   = DB::$ROOM->game_option->list[$item->name][0];
      $night = DB::$ROOM->game_option->list[$item->name][1];
    } else {
      $day   = TimeConfig::DEFAULT_DAY;
      $night = TimeConfig::DEFAULT_NIGHT;
    }

    $footer = sprintf(self::REALTIME, Text::Line($item->GetExplain()),
		      $item->name, $day, $item->name, $night);

    return sprintf(self::CHECKBOX, 'checkbox', $item->name, $item->name, $item->form_value,
		   $item->value ? ' checked' : '', $footer);
  }

  //セレクタ生成
  private static function GenerateSelector(SelectorRoomOptionItem $item) {
    $str = '';
    foreach ($item->GetItem() as $code => $child) {
      $label = $child instanceof RoomOptionItem ? $child->GetCaption() : $child;
      if (! is_string($code)) $code = $label;
      $str .= sprintf(self::SELECTOR, $code, $code == $item->value ? ' selected' : '', $label);
    }
    $explain = Text::Line($item->GetExplain());
    if (! OptionManager::$change && isset($item->javascript)) {
      self::$javascript[] = $item->javascript;
    }

    $format = <<<EOF
<select id="%s" name="%s"%s>
<optgroup label="%s">
%s</optgroup>
</select>
<span class="explain">(%s)</span>
EOF;

    return sprintf($format, $item->name, $item->form_name, $item->on_change, $item->label,
		   $str, $explain);
  }

  //グループ生成
  private static function GenerateGroup(RoomOptionItem $item) {
    $str = '';
    foreach ($item->GetItem() as $child) {
      $type = $child->type;
      if (! empty($type)) {
	switch ($type) {
	case 'radio':
	  $str .= self::GenerateCheckbox($child);
	  break;
	}
	$str .= Text::BRLF;
      }
    }

    return $str;
  }
}
