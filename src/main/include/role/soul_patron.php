<?php
/*
  ◆家神 (soul_patron)
  ○仕様
  ・追加役職：受援者の役職結果
*/
RoleManager::LoadFile('patron');
class Role_soul_patron extends Role_patron{
  public $result = 'PATRON_RESULT';
  function __construct(){ parent::__construct(); }

  protected function OutputResult(){
    global $ROOM;
    if($ROOM->date == 2) OutputSelfAbilityResult($this->result);
  }

  protected function AddDuelistRole($user){
    global $ROOM;
    $str = $this->GetActor()->handle_name . "\t" . $user->handle_name . "\t" . $user->main_role;
    $ROOM->SystemMessage($str, $this->result);
  }
}
