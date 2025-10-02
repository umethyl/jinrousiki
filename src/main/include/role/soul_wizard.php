<?php
/*
  ◆八卦見 (soul_wizard)
  ○仕様
  ・魔法：魂の占い師・精神鑑定士・ひよこ鑑定士・占星術師・騎士・死神・辻斬り・光妖精
  ・悪戯：迷彩 (公開者/光妖精)
*/
RoleManager::LoadFile('wizard');
class Role_soul_wizard extends Role_wizard{
  public $wizard_list = array(
    'soul_mage' => 'MAGE_DO', 'psycho_mage' => 'MAGE_DO', 'stargazer_mage' => 'MAGE_DO',
    'poison_guard' => 'GUARD_DO', 'doom_assassin' => 'ASSASSIN_DO',
    'soul_assassin' => 'ASSASSIN_DO', 'light_fairy' => 'FAIRY_DO', 'sex_mage' => 'MAGE_DO');
  public $result_list = array('MAGE_RESULT', 'GUARD_SUCCESS', 'GUARD_HUNTED', 'ASSASSIN_RESULT');
  public $bad_status = 'mind_open';
  function __construct(){ parent::__construct(); }

  function SetBadStatus($user){
    global $ROOM;
    $ROOM->event->{$this->bad_status} = true;
  }
}
