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
      foreach ($this->form_list as $key => $value) {
	if (DB::$ROOM->IsOption($value)) {
	  $this->value = $value;
	  break;
	}
      }
    }
  }

  function GetCaption() { return '霊界で配役を公開しない'; }

  function GetItem() {
    $stack = array(''               => OptionManager::GetClass('not_close_cast'),
		   'not_open_cast'  => OptionManager::GetClass('not_open_cast'),
		   'auto_open_cast' => OptionManager::GetClass('auto_open_cast'));
    foreach ($stack as $key => $item) {
      $item->value      = false;
      $item->form_name  = $this->form_name;
      $item->form_value = $key;
    }
    if (isset($stack[$this->value])) $stack[$this->value]->value = true;
    return $stack;
  }
}
