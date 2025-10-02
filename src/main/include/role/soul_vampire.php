<?php
/*
  ◆吸血姫 (soul_vampire)
  ○仕様
  ・対吸血：反射
  ・吸血：役職取得
*/
RoleManager::LoadFile('vampire');
class Role_soul_vampire extends Role_vampire{
  public $result = 'VAMPIRE_RESULT';
  function __construct(){ parent::__construct(); }

  protected function OutputResult(){
    global $ROOM;
    if($ROOM->date > 2) OutputSelfAbilityResult($this->result);
  }

  protected function InfectVampire($user){
    $this->AddSuccess($this->GetActor()->user_no, 'vampire_kill');
  }

  function Infect($user){
    global $ROOM;

    parent::Infect($user);
    $str = $this->GetActor()->GetHandleName($user->uname, $user->main_role);
    $ROOM->SystemMessage($str, $this->result);
  }
}
