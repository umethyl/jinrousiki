<?php
/*
  ◆サブ役職制限 (セレクタ)
*/
class Option_sub_role_limit extends SelectorRoomOptionItem {
  public $type = 'group';

  function __construct() {
    parent::__construct();
    $stack = array('no_sub_role' => 'no_sub_role');
    foreach (array('easy', 'normal', 'hard') as $name) {
      $stack[$name] = sprintf('%s_%s', $this->name, $name);
    }
    foreach ($stack as $name => $class) {
      $filter = OptionManager::GetClass($class);
      if (isset($filter) && $filter->enable) $this->form_list[$class] = $name;
    }
    if (OptionManager::$change) {
      foreach ($this->form_list as $key => $value) {
	if (DB::$ROOM->IsOption($key)) {
	  $this->value = $value;
	  break;
	}
      }
    }
  }

  function GetCaption() { return 'サブ役職制限'; }

  function GetItem() {
    $stack = array('no_sub_role' => OptionManager::GetClass('no_sub_role'),
		   'easy'        => OptionManager::GetClass('sub_role_limit_easy'),
		   'normal'      => OptionManager::GetClass('sub_role_limit_normal'),
		   'hard'        => OptionManager::GetClass('sub_role_limit_hard'),
		   ''            => OptionManager::GetClass('sub_role_limit_none'));
    foreach ($stack as $key => $item) {
      $item->form_name  = $this->form_name;
      $item->form_value = $key;
    }
    if (isset($stack[$this->value])) $stack[$this->value]->value = true;
    return $stack;
  }

  function LoadPost() {
    if (! isset($_POST[$this->name])) return false;
    $post = $_POST[$this->name];
    foreach ($this->form_list as $option => $value) {
      if ($value == $post) {
	RQ::$get->$option = true;
	array_push(RoomOption::${$this->group}, $option);
	break;
      }
    }
  }
}
