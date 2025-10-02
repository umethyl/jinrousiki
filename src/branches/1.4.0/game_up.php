<?php
require_once('include/init.php');
OutputHTMLHeader($SERVER_CONF->title . '[発言]', 'game_up');
?>
<link rel="stylesheet" id="day_night">
<script type="text/javascript" src="javascript/game_up.js"></script>
</head>
<body onLoad="set_focus(); reload_game();">
<a id="game_top"></a>
<?php
$url_argv = '?room_no=' . @(int)$_GET['room_no'] . '&auto_reload=' . @(int)$_GET['auto_reload'];
if(@$_GET['play_sound']  == 'on') $url_argv .= '&play_sound=on';
if(@$_GET['dead_mode']   == 'on') $url_argv .= '&dead_mode=on';
if(@$_GET['heaven_mode'] == 'on') $url_argv .= '&heaven_mode=on';
if(@$_GET['list_down']   == 'on') $url_argv .= '&list_down=on';
$header = '<form method="POST" action="game_play.php' . $url_argv . '" target="bottom" ';

//ページ読み込み時に自動でリロードするダミー送信フォーム
echo $header . 'name="reload_game"></form>'."\n";

//送信用フォーム
$submit = 'set_focus();';
//霊話モードの時は発言用フレームでリロード、書き込みしたときに真ん中のフレームもリロードする
if(@$_GET['heaven_mode'] == 'on') $submit .= 'reload_middle_frame();';
echo $header . 'class="input-say" name="send" onSubmit="' . $submit . '">'."\n";
?>
<table><tr>
<td><textarea name="say" rows="3" cols="70" wrap="soft"></textarea></td>
<td>
<input type="submit" onclick="setTimeout(&quot;auto_clear()&quot;, 10)" value="送信/リロード"><br>
<select name="font_type">
<option value="strong">強く発言する</option>
<option value="normal" selected>通常通り発言する</option>
<option value="weak">弱く発言する</option>
<option value="last_words">遺言を残す</option>
</select><br>
<?php echo '[<a class="vote" href="game_vote.php' . $url_argv . '">投票/占う/護衛</a>]' ?>
<a class="top-link" href="./" target="_top">TOP</a>
</td>
</tr></table>
</form>
</body></html>
