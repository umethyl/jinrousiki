<?php require_once('include/setting.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>��Ͽ�ϵ�ʤ꤫��[ȯ��]</title>
<link rel="stylesheet" href="css/game_up.css">
<link rel="stylesheet" id="day_night">
<script type="text/javascript" src="javascript/game_up.js"></SCRIPT>
</head>
<body onLoad="set_focus(); reload_game();">
<a name="game_top"></a>
<?php
$url_argv = '?room_no=' . (int)$_GET['room_no'] . '&auto_reload=' . (int)$_GET['auto_reload'];
if($_GET['play_sound'] == 'on')  $url_argv .= '&play_sound=on';
if($_GET['dead_mode'] == 'on')   $url_argv .= '&dead_mode=on';
if($_GET['heaven_mode'] == 'on') $url_argv .= '&heaven_mode=on';
if($_GET['list_down'] == 'on')   $url_argv .= '&list_down=on';
$header = '<form method="POST" action="game_play.php' . $url_argv . '#game_top" target="bottom" ';

//�ڡ����ɤ߹��߻��˼�ư�ǥ���ɤ�����ߡ������ե�����
echo $header . 'name="reload_game"></form>'."\n";

//�����ѥե�����
$submit = 'set_focus();';
//���å⡼�ɤλ���ȯ���ѥե졼��ǥ���ɡ��񤭹��ߤ����Ȥ��˿�����Υե졼������ɤ���
if($_GET['heaven_mode'] == 'on') $submit .= 'reload_middle_frame();';
echo $header . 'class="input-say" name="send" onSubmit="' . $submit . '">'."\n";
?>
<table><tr>
<td><textarea name="say" rows="3" cols="70" wrap="soft"></textarea></td>
<td>
<input type="submit" onclick="setTimeout(&quot;auto_clear()&quot;, 10)" value="����/�����"><br>
<select name="font_type">
<option value="strong">����ȯ������</option>
<option value="normal" selected>�̾��̤�ȯ������</option>
<option value="weak">�夯ȯ������</option>
<option value="last_words">�����Ĥ�</option>
</select><br>
<?php echo '[<a class="vote" href="game_vote.php' . $url_argv . '#game_top">��ɼ/�ꤦ/���</a>]'; ?>
<a class="top-link" href="index.php" target="_top">TOP</a>
</td>
</tr></table>
</form>
</body></html>
