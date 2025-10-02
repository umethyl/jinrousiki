<?php
/*
  ◆花妖精 (flower_fairy)
  ○仕様
  ・悪戯：死亡欄妨害 (花)
*/
RoleManager::LoadFile('fairy');
class Role_flower_fairy extends Role_fairy{
  public $result_header = 'FLOWERED';
  function __construct(){ parent::__construct(); }

  function FairyAction($user){
    global $ROOM, $USERS;

    $result = $this->result_header . '_' . GetRandom(range('A', 'Z'));
    $ROOM->SystemMessage($USERS->GetHandleName($user->uname, true), $result);
  }
}
