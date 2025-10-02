<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('ROOM_CONF', 'GAME_CONF', 'CAST_CONF', 'GAME_OPT_MESS', 'ROLE_DATA');
$INIT_CONF->LoadFile('game_vote_functions', 'request_class');
OutputHTMLHeader('裏・闇鍋モード配役テストツール', 'role_table');
OutputRoleTestForm();
if($_POST['command'] == 'role_test'){
  $RQ_ARGS = new RequestBase();
  $RQ_ARGS->TestItems->is_virtual_room = true;
  $stack->game_option = array('chaos_verso');
  $stack->option_role = array();

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
<form method="POST" action="chaos_verso.php">
<input type="hidden" name="command" value="role_test">
<label>人数</label><input type="text" name="user_count" size="3" value="20">
<label>試行回数</label><input type="text" name="try_count" size="2" value="100">
<input type="submit" value=" 実 行 "><br>
</form>

EOF;
}
