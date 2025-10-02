<?php
/*
  ◆初日の夜は身代わり君 (セレクタ)
*/
class Option_dummy_boy_selector extends SelectorRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'group';
  public $form_list = array('dummy_boy' => 'on', 'gm_login' => 'gm_login');

  function __construct() {
    parent::__construct();
    $this->value = GameOptionConfig::$default_dummy_boy;
    if (OptionManager::$change) $this->enable = false;
  }

  function GetCaption() { return '初日の夜は身代わり君'; }

  function GetExplain() { return '配役は<a href="info/rule.php">ルール</a>を確認して下さい'; }

  function GetItem() {
    $stack = array(''         => new Option_no_dummy_boy(),
		   'on'       => OptionManager::GetClass('dummy_boy'),
		   'gm_login' => OptionManager::GetClass('gm_login'));
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
      if ($post == $value) {
	RQ::$get->$option = true;
	array_push(RoomOption::${$this->group}, $option);
	break;
      }
    }
  }
}

/*
  ◆身代わり君なし
*/
class Option_no_dummy_boy extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'radio';

  function GetCaption() { return '身代わり君なし'; }
}
