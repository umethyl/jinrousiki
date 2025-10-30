<?php
//-- HTML 生成クラス (OptionForm 拡張) --//
final class OptionFormHTML {
  //村作成オプションフォーム出力
  public static function Output(Option $filter, $class, $str) {
    Text::Printf(self::Get(), $class, $filter->name, $filter->GetCaption(), Message::COLON, $str);
  }

  //境界線出力
  public static function OutputSeparator() {
    Text::Output(self::GetSeparator());
  }

  //表示制御リンク出力
  public static function OutputToggle($group, $name) {
    Text::Printf(self::GetToggle(),
      $group, $group, $group, $name, $group, OptionMessage::TOGGLE_OFF,
      $group, $group, $name, $group, OptionMessage::TOGGLE_ON
    );
  }

  //JavaScript 出力
  public static function OutputJavaScript(array $list) {
    JavaScriptHTML::OutputHeader();
    foreach ($list as $code) {
      Text::Output($code);
    }
    JavaScriptHTML::OutputFooter();
  }

  //チェックボックス生成
  public static function GenerateCheckbox(OptionCheckbox $filter, $type, $footer) {
    return sprintf(self::GetCheckbox(),
      $type, $filter->name, $filter->form_name, $filter->form_value,
      FormHTML::Checked($filter->value), $footer
    );
  }

  //テキストボックス生成
  public static function GenerateTextbox(OptionText $filter) {
    $size = sprintf('%s_input', $filter->name);
    $str  = $filter->GetExplain();
    if (RoomOptionManager::IsChange()) {
      $name        = Text::CutPop($filter->name);
      $placeholder = '';
      $value       = DB::$ROOM->$name;
    } else {
      $placeholder = $filter->GetPlaceholder();
      $value       = null;
    }

    return sprintf(self::GetTextbox(),
      $filter->type, $filter->name, $filter->name, RoomConfig::$$size,
      $placeholder, $value,
      isset($str) ? HTML::GenerateSpan($str, 'explain') : ''
    );
  }

  //テキストボックス(チェック付き用)生成
  public static function GenerateTextCheckbox(OptionTextCheckbox $filter) {
    if (RoomOptionManager::IsChange()) {
      $placeholder = '';
      $value       = $filter->input_value;
    } else {
      $placeholder = $filter->GetPlaceholder();
      $value       = null;
    }

    return sprintf(self::GetTextCheckbox(),
      OptionFormType::TEXT, $filter->name, $filter->GetTextSize(),
      $placeholder, $value, Message::SPACER, $filter->GetExplain()
    );
  }

  //テキストボックス(制限付き用)生成
  public static function GenerateLimitedCheckbox(OptionLimitedCheckbox $filter) {
    return sprintf(self::GetLimitedCheckbox(),
      Text::ConvertLine($filter->GetExplain()), Message::SPACER, OptionFormType::TEXT,
      $filter->name, $filter->GetLimitedCount(), $filter->GetLimitedFormCaption()
    );
  }

  //テキストボックス生成 (リアルタイム制用)
  public static function GenerateRealtime(Option_real_time $filter, $day, $night) {
    return sprintf(self::GetRealTime(),
      Text::ConvertLine($filter->GetExplain()), Message::SPACER,
      OptionMessage::REALTIME_DAY,   Message::COLON, OptionFormType::TEXT,
      $filter->name, $day,   Message::MINUTE,
      OptionMessage::REALTIME_NIGHT, Message::COLON, OptionFormType::TEXT,
      $filter->name, $night, Message::MINUTE
    );
  }

  //セレクタ生成
  public static function GenerateSelector(OptionSelector $filter, $str) {
    return sprintf(self::GetSelector(),
      $filter->name, $filter->form_name, $filter->on_change, $filter->label,
      $str, Text::ConvertLine($filter->GetExplain())
    );
  }

  //セレクタ個別項目生成
  public static function GenerateSelectorOption($code, $selected, $label) {
    return Text::Format(self::GetSelectorOption(), $code, $selected, $label);
  }

  //村作成オプションフォームタグ
  private static function Get() {
    return <<<EOF
<tr%s>
  <td class="title"><label for="%s">%s%s</label></td>
  <td>%s</td>
</tr>
EOF;
  }

  //境界線タグ
  private static function GetSeparator() {
    return '<tr><td colspan="2"><hr></td></tr>';
  }

  //表示制御リンクタグ
  private static function GetToggle() {
    return <<<EOF
<tr id="%s_on" class="%s">
  <td class="title"><label onClick="toggle_option_display('%s', true)">%s</label></td>
  <td onClick="toggle_option_display('%s', true)"><a href="javascript:void(0)">%s</a></td>
</tr>
<tr id="%s_off">
  <td class="title"><label onClick="toggle_option_display('%s', false)">%s</label></td>
  <td onClick="toggle_option_display('%s', false)"><a href="javascript:void(0)">%s</a></td>
</tr>
EOF;
  }

  //チェックボックスタグ
  public static function GetCheckbox() {
    return '<input type="%s" id="%s" name="%s" value="%s"%s> <span class="explain">%s</span>';
  }

  //テキストボックスタグ
  private static function GetTextbox() {
    return '<input type="%s" id="%s" name="%s" size="%d" placeholder="%s" value="%s">%s';
  }

  //テキストボックスタグ(チェックボックス付き用)
  private static function GetTextCheckbox() {
    return '<input type="%s" name="%s_input" size="%d" placeholder="%s" value="%s">%s%s';
  }

  //テキストボックスタグ(制限付き用)
  private static function GetLimitedCheckbox() {
    return '%s%s(<input type="%s" name="%s_count" size="2" maxlength="2" value="%d">%s)';
  }

  //テキストボックスタグ(リアルタイム制用)
  private static function GetRealTime() {
    return '%s%s(' .
      '%s%s<input type="%s" name="%s_day" size="2" maxlength="2" value="%d">%s ' .
      '%s%s<input type="%s" name="%s_night" size="2" maxlength="2" value="%d">%s)';
  }

  //セレクタタグ
  private static function GetSelector() {
    return <<<EOF
<select id="%s" name="%s"%s>
<optgroup label="%s">
%s</optgroup>
</select>
<span class="explain">(%s)</span>
EOF;
  }

  //セレクタ個別項目タグ
  private static function GetSelectorOption() {
    return '  <option value="%s"%s>%s</option>';
  }
}
