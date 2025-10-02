<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('ROOM_CONF', 'GAME_CONF', 'CAST_CONF', 'GAME_OPT_MESS', 'ROLE_DATA');
$INIT_CONF->LoadFile('game_vote_functions', 'request_class');
OutputHTMLHeader('配役テストツール', 'role_table');
OutputRoleTestForm();
if($_POST['command'] == 'role_test'){
  $RQ_ARGS = new RequestBase();
  $RQ_ARGS->TestItems->is_virtual_room = true;
  $stack->game_option = array();
  $stack->option_role = array();
  switch($_POST['game_option']){
  case 'chaos':
  case 'chaosfull':
  case 'chaos_hyper':
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
  if($_POST['festival'] == 'on') $stack->game_option[] = ' festival';

  if(array_search($_POST['replace_human'], $ROOM_CONF->replace_human_list) !== false){
    $stack->option_role[] = $_POST['replace_human'];
  }
  if(array_search($_POST['topping'], $ROOM_CONF->topping_list) !== false){
    $stack->option_role[] = 'topping:' . $_POST['topping'];
  }
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
<label>人数</label><input type="text" name="user_count" size="3" value="20">
<label>試行回数</label><input type="text" name="try_count" size="2" value="100">
<input type="submit" value=" 実 行 "><br>
<input type="radio" name="game_option" value="">普通村
<input type="radio" name="game_option" value="chaos">闇鍋
<input type="radio" name="game_option" value="chaosfull">真・闇鍋
<input type="radio" name="game_option" value="chaos_hyper" checked>超・闇鍋
<input type="radio" name="game_option" value="duel">決闘
<input type="radio" name="game_option" value="duel_auto_open_cast">自動公開決闘
<input type="radio" name="game_option" value="duel_not_open_cast">非公開決闘
<input type="radio" name="game_option" value="gray_random">グレラン
<input type="radio" name="game_option" value="quiz">クイズ<br>
<input type="radio" name="topping" value="" checked>追加無し

EOF;

  foreach($ROOM_CONF->topping_list as $mode){
    echo <<<EOF
<input type="radio" name="topping" value="{$mode}">{$GAME_OPT_MESS->{'topping_' . $mode}}

EOF;
  }

  echo <<<EOF
<br>
<input type="radio" name="replace_human" value="" checked>置換無し

EOF;

  foreach($ROOM_CONF->replace_human_list as $mode){
    echo <<<EOF
<input type="radio" name="replace_human" value="{$mode}">{$GAME_OPT_MESS->$mode}

EOF;
  }
  echo <<<EOF
<input type="checkbox" name="festival" value="on">お祭り
</form>

EOF;
}
