<?php
//-- GameUp 出力クラス --//
class GameUp {
  //出力
  static function Output() {
    HTML::OutputHeader(ServerConfig::TITLE . GameUpMessage::TITLE, 'game_up');
    HTML::OutputJavaScript('game_up');
    self::OutputHeader();
    self::OutputFormHeader();
    self::OutputForm();
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    echo <<<EOF
<link rel="stylesheet" id="scene">
</head>
<body onLoad="set_focus(); reload_game();">
<a id="game_top"></a>

EOF;
  }

  //フォームヘッダ出力
  private static function OutputFormHeader() {
    //送信用フォーム
    $format = '<form method="post" action="game_play.php%s" target="bottom" ';
    $header = sprintf($format, RQ::Get()->url);
    Text::Output($header . 'name="reload_game"></form>'); //自動リロード用ダミー送信フォーム

    //霊話モードの時は発言用フレームでリロード・書き込みしたときに真ん中のフレームもリロードする
    $submit = $header . 'class="input-say" name="send" onSubmit="';
    if (RQ::Get()->heaven_mode) $submit .= 'reload_middle_frame();';
    Text::Output($submit . 'set_focus();">');
  }

  //フォーム本体出力
  private static function OutputForm() {
    $format = <<<EOF
<table><tr>
<td><textarea name="say" rows="3" cols="70" wrap="soft"></textarea></td>
<td>
<input type="submit" onclick="setTimeout(&quot;auto_clear()&quot;, 10)" value="%s"><br>
<select name="font_type">
<option value="strong">%s</option>
<option value="normal" selected>%s</option>
<option value="weak">%s</option>
<option value="last_words">%s</option>
</select><br>
[<a class="vote" href="game_vote.php%s">%s</a>]
<a class="top-link" href="./" target="_top">%s</a>
</td>
</tr></table>
</form>
EOF;

    printf($format . Text::LF, GameUpMessage::SUBMIT,
	   GameUpMessage::STRONG, GameUpMessage::NORMAL, GameUpMessage::WEAK,
	   GameUpMessage::LAST_WORDS,
	   RQ::Get()->url, GameUpMessage::VOTE, GameUpMessage::TOP);
  }
}
