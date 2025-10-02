<?php
/*
  ◆最大人数 (max_user)
*/
class Option_max_user extends OptionSelector {
  public $group = OptionGroup::NONE;

  protected function LoadValue() {
    if (OptionManager::IsChange()) {
      $this->value = DB::$ROOM->max_user;
    } else {
      $this->value = RoomConfig::$default_max_user;
    }
  }

  protected function LoadConfName() {
    $this->conf_name = RoomConfig::$max_user_list;
  }

  public function LoadPost() {
    RQ::Get()->ParsePostInt($this->name);
  }

  public function GetCaption() {
    return '最大人数';
  }

  public function GetExplain() {
    return '配役は<a href="info/rule.php">ルール</a>を確認して下さい';
  }
}
