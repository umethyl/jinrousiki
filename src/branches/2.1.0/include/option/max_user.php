<?php
/*
  ◆最大人数 (max_user)
*/
class Option_max_user extends SelectorRoomOptionItem {
  public $group = RoomOption::NOT_OPTION;

  function __construct() {
    parent::__construct();
    $this->conf_name = RoomConfig::$max_user_list;
    $this->value     = OptionManager::$change ? DB::$ROOM->max_user : RoomConfig::$default_max_user;
  }

  function GetCaption() { return '最大人数'; }

  function GetExplain() { return '配役は<a href="info/rule.php">ルール</a>を確認して下さい'; }

  function LoadPost() { RQ::$get->Parse('intval', 'post.' . $this->name); }
}
