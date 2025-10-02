<?php
//error_reporting(E_ALL);
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('ROOM_CONF', 'GAME_CONF', 'CAST_CONF', 'GAME_OPT_MESS', 'ROLE_DATA');
$INIT_CONF->LoadFile('game_vote_functions', 'request_class');
OutputHTMLHeader('配役テストツール', 'role_table');
OutputRoleTestForm();
if(array_key_exists('command', $_POST) && $_POST['command'] == 'role_test'){
  $RQ_ARGS = new RequestBase();
  $RQ_ARGS->TestItems->is_virtual_room = true;
  $stack->game_option = array('dummy_boy');
  $stack->option_role = array();
  switch($_POST['game_option']){
  case 'chaos':
  case 'chaosfull':
  case 'chaos_hyper':
  case 'chaos_verso':
  case 'duel':
  case 'gray_random':
  case 'quiz':
    $stack->game_option[] = $_POST['game_option'];
    break;

  case 'duel_auto_open_cast':
    $stack->game_option[] = 'duel';
    $stack->option_role[] = 'auto_open_cast';
    break;

  case 'duel_not_open_cast':
    $stack->game_option[] = 'duel';
    $stack->option_role[] = 'not_open_cast';
    break;
  }
  foreach(array('festival') as $option){
    if(array_key_exists($option, $_POST) && $_POST[$option] == 'on'){
      $stack->game_option[] = $option;
    }
  }
  foreach(array('gerd', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'possessed_wolf',
		'fox', 'child_fox', 'cupid', 'medium', 'mania', 'detective') as $option){
    if(array_key_exists($option, $_POST) && $_POST[$option] == 'on'){
      $stack->option_role[] = $option;
    }
  }

  foreach(array('replace_human', 'change_common', 'change_mad', 'change_cupid') as $option){
    if(array_search($_POST[$option], $ROOM_CONF->{$option.'_list'}) !== false){
      $stack->option_role[] = $_POST[$option];
    }
  }
  foreach(array('topping', 'boost_rate') as $option){
    if(array_search($_POST[$option], $ROOM_CONF->{$option.'_list'}) !== false){
      $stack->option_role[] = $option . ':' . $_POST[$option];
    }
  }
  if($_POST['limit_off'] == 'on') $CAST_CONF->chaos_role_group_rate_list = array();

  $RQ_ARGS->TestItems->test_room['game_option'] = implode(' ', $stack->game_option);
  $RQ_ARGS->TestItems->test_room['option_role'] = implode(' ', $stack->option_role);
  $ROOM = new Room($RQ_ARGS);
  $ROOM->LoadOption();

  $user_count = (int)$_POST['user_count'];
  $try_count  = (int)$_POST['try_count'];
  $str = '%0' . strlen($try_count) . 'd回目: ';
  for($i = 1; $i <= $try_count; $i++){
    printf($str, $i);
    $role_list = GetRoleList($user_count);
    if($role_list == '') break;
    PrintData(GenerateRoleNameList(array_count_values($role_list), true));
  }
}
OutputHTMLFooter(true);

function OutputRoleTestForm(){
  global $ROOM_CONF, $GAME_OPT_MESS;

  echo <<<EOF
</head>
<body>
<form method="POST" action="role_test.php">
<input type="hidden" name="command" value="role_test">
<label>人数</label><input type="text" name="user_count" size="2" value="20">
<label>試行回数</label><input type="text" name="try_count" size="2" value="100">
<input type="submit" value=" 実 行 "><br>
<input type="radio" name="game_option" value="">普通
<input type="radio" name="game_option" value="chaos">闇鍋
<input type="radio" name="game_option" value="chaosfull">真・闇鍋
<input type="radio" name="game_option" value="chaos_hyper" checked>超・闇鍋
<input type="radio" name="game_option" value="chaos_verso">裏・闇鍋
<input type="radio" name="game_option" value="duel">決闘
<input type="radio" name="game_option" value="duel_auto_open_cast">自動公開決闘
<input type="radio" name="game_option" value="duel_not_open_cast">非公開決闘
<input type="radio" name="game_option" value="gray_random">グレラン
<input type="radio" name="game_option" value="quiz">クイズ<br>

EOF;

  foreach(array('topping', 'boost_rate') as $option){
    echo <<<EOF
<input type="radio" name="{$option}" value="" checked>標準

EOF;

    $count = 0;
    foreach($ROOM_CONF->{$option.'_list'} as $mode){
      $count++;
      if($count > 0 && $count % 9 == 0) echo "<br>\n";
      echo <<<EOF
<input type="radio" name="{$option}" value="{$mode}">{$GAME_OPT_MESS->{$option.'_'.$mode}}

EOF;
    }
    echo "<br>\n";
  }

  foreach(array('replace_human', 'change_common', 'change_mad', 'change_cupid') as $option){
    echo <<<EOF
<input type="radio" name="{$option}" value="" checked>標準

EOF;

    $count = 0;
    foreach($ROOM_CONF->{$option.'_list'} as $mode){
      $count++;
      if($count > 0 && $count % 9 == 0) echo "<br>\n";
      echo <<<EOF
<input type="radio" name="{$option}" value="{$mode}">{$GAME_OPT_MESS->$mode}

EOF;
    }
    echo "<br>\n";
  }

  $stack = array(
     'gerd' => 'ゲルト君', 'poison' => '毒', 'assassin' => '暗殺', 'wolf' => '人狼', 
     'boss_wolf' => '白狼', 'poison_wolf' => '毒狼', 'possessed_wolf' => '憑狼', 'fox' => '妖狐',
     'child_fox' => '子狐', 'cupid' => 'QP', 'medium' => '巫女', 'mania' => 'マニア',
     'detective' => '探偵', 'festival' => 'お祭り', 'limit_off' => 'リミッタオフ');
  foreach($stack as $option => $name){
    echo <<<EOF
<input type="checkbox" value="on" name="{$option}">{$name}

EOF;
  }
  echo "</form>\n";
}
