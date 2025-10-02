<?php
/*
  ◆サブ役職制限なし
*/
class Option_sub_role_limit_none extends CheckRoomOptionItem {
  public $type = 'radio';

  function GetCaption() { return 'サブ役職制限なし'; }
}
