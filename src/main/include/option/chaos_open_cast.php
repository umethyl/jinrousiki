<?php
/*
  ◆配役を通知する (セレクタ)
*/
class Option_chaos_open_cast extends SelectorRoomOptionItem {
  public $type = 'group';

  function __construct() {
    parent::__construct();
    foreach (array('camp', 'role', 'full') as $name) {
      $class  = sprintf('%s_%s', $this->name, $name);
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

  function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) return false;

    $post = RQ::Get()->{$this->name};
    foreach ($this->form_list as $option => $value) {
      if ($value == $post) {
	RQ::Set($option, true);
	array_push(RoomOption::${$this->group}, $option);
	break;
      }
    }
  }

  function GetItem() {
    $stack = array(''     => OptionManager::GetClass('chaos_open_cast_none'),
		   'camp' => OptionManager::GetClass('chaos_open_cast_camp'),
		   'role' => OptionManager::GetClass('chaos_open_cast_role'),
		   'full' => OptionManager::GetClass('chaos_open_cast_full'));
    foreach ($stack as $key => $item) {
      $item->form_name  = $this->form_name;
      $item->form_value = $key;
    }
    if (isset($stack[$this->value])) $stack[$this->value]->value = true;
    return $stack;
  }

  function GetCaption() { return '配役を通知する'; }

  protected function GetURL() { return 'chaos.php#' . $this->name; }
}
