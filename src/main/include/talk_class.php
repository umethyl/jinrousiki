<?php
class Talk{
  function Talk(){ $this->__construct(); }
  function __construct(){
    $this->ParseLocation();
    $this->ParseSentence();
  }

  function ParseLocation($location = NULL){
    if(! is_null($location)) $this->location = $location; //初期化処理

    list($scene, $type) = explode(' ', $this->location);
    $this->scene = $scene;
    $this->type  = $type;
  }

  function ParseSentence($sentence = NULL){
    global $GAME_CONF, $MESSAGE;

    is_null($sentence) ? $sentence = $this->sentence : $this->sentence = $sentence; //初期化処理

    switch($this->uname){ //システムユーザ系の処理
    case 'system':
      $action = strtok($sentence, "\t");
      switch($action){
      case 'MORNING':
	$sentence = strtok("\t");
	$this->sentence = "{$MESSAGE->morning_header} {$sentence} {$MESSAGE->morning_footer}";
	return;

      case 'NIGHT':
	$this->sentence = $MESSAGE->night;
	return;
      }
      return;

    case 'dummy_boy':
      if($this->type == 'system') break;
      if($this->type == $this->uname){
	if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
	$this->sentence = $MESSAGE->dummy_boy . $sentence;
      }
      return;
    }

    if($this->type == 'system'){ //投票データ系
      $this->action = strtok($sentence, "\t");
      $action = strtolower($this->action);
      switch($this->action){ //大文字小文字をきちんと区別してマッチングする
      case 'OBJECTION':
	$this->sentence = ' ' . $MESSAGE->objection;
	return;

      case 'GAMESTART_DO':
	return;

      case 'VOODOO_KILLER_DO':
	$this->class = 'mage-do';
	break;

      case 'CHILD_FOX_DO':
	$action = 'mage_do';
	$this->class = 'mage-do';
	break;

      case 'DREAM_EAT':
	$action = 'wolf_eat';
	$this->class = 'wolf-eat';
	break;

      case 'JAMMER_MAD_DO':
      case 'VOODOO_MAD_DO':
      case 'VOODOO_FOX_DO':
      case 'TRAP_MAD_DO':
      case 'POSSESSED_DO':
	$action = array_shift(explode('_', $action)) . '_do';
	$this->class = 'wolf-eat';
	break;

      case 'TRAP_MAD_NOT_DO':
	$this->class = 'wolf-eat';
	$this->sentence = ' ' . $MESSAGE->trap_not_do;
	return;

      case 'POSSESSED_NOT_DO':
	$this->class = 'wolf-eat';
	$this->sentence = ' ' . $MESSAGE->possessed_not_do;
	return;

      case 'REPORTER_DO':
      case 'ANTI_VOODOO_DO':
	$this->class = 'guard-do';
	break;

      case 'POISON_CAT_DO':
	$action = 'revive_do';
	$this->class = 'revive-do';
	break;

      case 'POISON_CAT_NOT_DO':
	$this->class = 'revive-do';
	$this->sentence = ' ' . $MESSAGE->revive_not_do;
	return;

      case 'ASSASSIN_NOT_DO':
	$this->class = 'assassin-do';
	$this->sentence = ' ' . $MESSAGE->assassin_not_do;
	return;

      case 'VAMPIRE_DO':
	$action = 'wolf_eat';
	$this->class = 'vampire-do';
	break;

      case 'OGRE_NOT_DO':
	$this->class = 'ogre-do';
	$this->sentence = ' ' . $MESSAGE->ogre_not_do;
	return;

      default:
	$this->class = strtr($action, '_', '-');
	break;
      }
      $this->sentence = ' は ' . strtok("\t") . ' ' . $MESSAGE->$action;
      return;
    }
  }
}
