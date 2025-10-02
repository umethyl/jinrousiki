<?php
//-- HTML 生成クラス (OptionForm 拡張) --//
class OptionFormHTML {
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

  //テキストボックス生成
  public static function GenerateTextbox(OptionText $filter) {
    $size = sprintf('%s_input', $filter->name);
    $str  = $filter->GetExplain();
    if (OptionManager::IsChange()) {
      $name  = Text::CutPop($filter->name);
      $value = DB::$ROOM->$name;
    } else {
      $value = null;
    }

    return sprintf(self::GetTextbox(),
      $filter->type, $filter->name, $filter->name, RoomConfig::$$size, $value,
      isset($str) ? HTML::GenerateSpan($str, 'explain') : ''
    );
  }

  //チェックボックス生成
  public static function GenerateCheckbox(OptionCheckbox $filter, $type, $footer) {
    return sprintf(self::GetCheckbox(),
      $type, $filter->name, $filter->form_name, $filter->form_value,
      HTML::GenerateChecked($filter->value), $footer
    );
  }

  //時刻入力フォーム生成 (リアルタイム制用)
  public static function GenerateRealtime(Option_real_time $filter, $day, $night) {
    return sprintf(self::GetRealTime(),
      Text::ConvertLine($filter->GetExplain()), Message::SPACER,
      OptionMessage::REALTIME_DAY, Message::COLON,
      $filter->name, $day, Message::MINUTE,
      OptionMessage::REALTIME_NIGHT, Message::COLON,
      $filter->name, $night, Message::MINUTE
    );
  }

  //発言数フォーム生成 (発言数制限制用)
  public static function GenerateLimitTalk(Option_limit_talk $filter, $count) {
    return sprintf(self::GetLimitTalk(),
      Text::ConvertLine($filter->GetExplain()), Message::SPACER, $filter->name, $count
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

  //JavaScript 出力
  public static function OutputJavaScript(array $list) {
    HTML::OutputJavaScriptHeader();
    foreach ($list as $code) {
      Text::Output($code);
    }
    HTML::OutputJavaScriptFooter();
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

  //テキストボックスタグ
  private static function GetTextbox() {
    return '<input type="%s" id="%s" name="%s" size="%d" value="%s">%s';
  }

  //チェックボックスタグ
  public static function GetCheckbox() {
    return '<input type="%s" id="%s" name="%s" value="%s"%s> <span class="explain">%s</span>';
  }

  //時刻入力フォームタグ
  private static function GetRealTime() {
    return '(%s%s' .
      '%s%s<input type="text" name="%s_day" size="2" maxlength="2" value="%d">%s ' .
      '%s%s<input type="text" name="%s_night" size="2" maxlength="2" value="%d">%s)';
  }

  //発言数フォームタグ
  private static function GetLimitTalk() {
    return '(%s%s<input type="text" name="%s_count" size="2" maxlength="2" value="%d">)';
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
}
