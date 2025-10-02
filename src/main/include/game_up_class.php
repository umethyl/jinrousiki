<?php
//-- GameUp 出力クラス --//
class GameUp {
  static function Output() {
    HTML::OutputHeader(ServerConfig::TITLE . '[発言]', 'game_up');
    HTML::OutputJavaScript('game_up');
    $format = <<<EOF
<link rel="stylesheet" id="scene">
</head>
<body onLoad="set_focus(); reload_game();">
<a id="game_top"></a>
%s
%s
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
[<a class="vote" href="game_vote.php%s">投票/占う/護衛</a>]
<a class="top-link" href="./" target="_top">TOP</a>
</td>
</tr></table>
</form>%s
EOF;

    //送信用フォーム
    $form_header = '<form method="POST" action="game_play.php%s" target="bottom" ';
    $header = sprintf($form_header, RQ::$get->url);
    $reload = $header . 'name="reload_game"></form>'; //自動リロード用ダミー送信フォーム

    //霊話モードの時は発言用フレームでリロード・書き込みしたときに真ん中のフレームもリロードする
    $submit = $header . 'class="input-say" name="send" onSubmit="';
    if (RQ::$get->heaven_mode) $submit .= 'reload_middle_frame();';
    $submit .= 'set_focus();">';
    printf($format, $reload, $submit, RQ::$get->url, "\n");
    HTML::OutputFooter();
  }
}
