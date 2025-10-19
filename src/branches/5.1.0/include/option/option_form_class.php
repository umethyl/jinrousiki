<?php
//-- オプション入力画面表示クラス --//
class OptionForm {
  private static $javascript = []; //javascript 出力情報

  //出力
  public static function Output() {
    $class = '';
    foreach (OptionFormData::$order as $group => $name) {
      if (false === is_int($group)) {
	$class = sprintf(' class="%s"', $group); //class 切り替え
      }

      if (null === $name) {
	if ('duel' !== $group) {
	  self::OutputSeparator($group);
	}
      } else {
	self::OutputForm($name, $class);
      }
    }

    if (count(self::$javascript) > 0) {
      OptionFormHTML::OutputJavaScript(self::$javascript);
    }
  }

  //フォーム出力 (振り分け処理用)
  private static function OutputForm($name, $class) {
    $filter = OptionLoader::Load($name);
    if (false === $filter->enable || false === isset($filter->type)) {
      return;
    }

    switch ($filter->type) {
    case OptionFormType::CHECKBOX:
    case OptionFormType::RADIO:
      $str = self::GenerateCheckbox($filter);
      break;

    case OptionFormType::LIMITED_CHECKBOX:
      $str = self::GenerateLimitedCheckbox($filter);
      break;

    case OptionFormType::REALTIME:
      $str = self::GenerateRealtime($filter);
      break;

    case OptionFormType::TEXT:
    case OptionFormType::PASSWORD:
      $str = self::GenerateTextbox($filter);
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
    if (RoomOptionManager::IsChange()) {
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

  //チェックボックス生成
  private static function GenerateCheckbox(OptionCheckbox $filter) {
    $footer = isset($filter->footer) ? $filter->footer : $filter->GetExplain();
    return OptionFormHTML::GenerateCheckbox($filter, $filter->type, Text::ConvertLine($footer));
  }

  //チェックボックス生成 (制限付き用)
  private static function GenerateLimitedCheckbox(OptionLimitedCheckbox $filter) {
    $footer = OptionFormHTML::GenerateLimitedCheckbox($filter);
    return OptionFormHTML::GenerateCheckbox($filter, OptionFormType::CHECKBOX, $footer);
  }

  //チェックボックス生成 (リアルタイム制専用)
  private static function GenerateRealtime(Option_real_time $filter) {
    if (RoomOptionManager::IsChange()) {
      $day   = DB::$ROOM->game_option->list[$filter->name][0];
      $night = DB::$ROOM->game_option->list[$filter->name][1];
    } else {
      $day   = TimeConfig::DEFAULT_DAY;
      $night = TimeConfig::DEFAULT_NIGHT;
    }
    $footer = OptionFormHTML::GenerateRealtime($filter, $day, $night);

    return OptionFormHTML::GenerateCheckbox($filter, OptionFormType::CHECKBOX, $footer);
  }

  //テキストボックス生成
  private static function GenerateTextbox(OptionText $filter) {
    return OptionFormHTML::GenerateTextbox($filter);
  }

  //セレクタ生成
  private static function GenerateSelector(OptionSelector $filter) {
    $str = '';
    foreach ($filter->GetItem() as $code => $child) {
      $label = $child instanceof Option ? $child->GetCaption() : $child;
      if (false === is_string($code)) {
	$code = $label;
      }
      $selected = HTML::GenerateSelected($code == $filter->value);
      $str .= OptionFormHTML::GenerateSelectorOption($code, $selected, $label);
    }

    if (false === RoomOptionManager::IsChange() && isset($filter->javascript)) {
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
