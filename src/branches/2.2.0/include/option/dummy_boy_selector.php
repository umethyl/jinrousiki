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

  function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) return false;

    $post = RQ::Get()->{$this->name};
    foreach ($this->form_list as $option => $form_value) {
      if ($post == $form_value) {
	RQ::Set($option, true);
	array_push(RoomOption::${$this->group}, $option);
	break;
      }
    }
  }

  function GetItem() {
    $stack = array('' => new Option_no_dummy_boy());
    foreach ($this->form_list as $option => $form_value) {
      $item = OptionManager::GetClass($option);
      if ($item->enable) $stack[$form_value] = $item;
    }

    foreach ($stack as $form_value => $item) {
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

  function GetCaption() { return '初日の夜は身代わり君'; }

  function GetExplain() { return '配役は<a href="info/rule.php">ルール</a>を確認して下さい'; }
}

/*
  ◆身代わり君なし
*/
class Option_no_dummy_boy extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'radio';

  function GetCaption() { return '身代わり君なし'; }
}
