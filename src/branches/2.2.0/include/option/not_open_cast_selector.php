<?php
/*
  ◆霊界で配役を公開しない (セレクタ)
*/
class Option_not_open_cast_selector extends SelectorRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type = 'group';
  public $form_list = array('not_open_cast', 'auto_open_cast');

  function __construct() {
    parent::__construct();
    $this->value = GameOptionConfig::$default_not_open_cast;
    if (OptionManager::$change) {
      foreach ($this->form_list as $option) {
	if (DB::$ROOM->IsOption($option)) {
	  $this->value = $option;
	  break;
	}
      }
    }
  }

  function GetItem() {
    $stack = array('' => OptionManager::GetClass('not_close_cast'));
    foreach ($this->form_list as $option) {
      $item = OptionManager::GetClass($option);
      if ($item->enable) $stack[$option] = $item;
    }

    foreach ($stack as $form_value => $item) {
      $item->value      = false;
      $item->form_name  = $this->form_name;
      $item->form_value = $form_value;
    }

    if (array_key_exists($this->value, $stack)) { //チェック位置判定
      $stack[$this->value]->value = true;
    } else {
      $stack['']->value = true;
    }

    return $stack;
  }

  function GetCaption() { return '霊界で配役を公開しない'; }
}
