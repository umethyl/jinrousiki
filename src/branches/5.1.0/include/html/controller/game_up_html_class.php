<?php
//-- HTML 生成クラス (GameUp 拡張) --//
final class GameUpHTML {
  //出力
  public static function Output() {
    HTML::OutputHeader(ServerConfig::TITLE . GameUpMessage::TITLE, 'game_up');
    GameHTML::OutputSceneCSS();
    HTML::OutputJavaScript('game_up');
    HTML::OutputBodyHeader(null, 'set_focus();reload_game();');
    GameHTML::OutputGameTop();
    self::OutputForm();
    HTML::OutputFooter();
  }

  //フォーム出力
  private static function OutputForm() {
    Text::Printf(self::GetForm(),
      RQ::Get()->url, RQ::Get()->url, RQ::Get()->heaven_mode ? 'reload_middle_frame();' : '',
      Security::GetToken(RQ::Get()->room_no),
      RequestDataTalk::SENTENCE, GameMessage::SUBMIT,
      RequestDataTalk::VOICE,
      TalkVoice::STRONG, GameUpMessage::STRONG,
      TalkVoice::NORMAL, HTML::GenerateSelected(true), GameUpMessage::NORMAL,
      TalkVoice::WEAK, GameUpMessage::WEAK,
      TalkVoice::SECRET, GameUpMessage::SECRET,
      TalkVoice::LAST_WORDS, GameUpMessage::LAST_WORDS,
      RQ::Get()->url, GameUpMessage::VOTE, GameUpMessage::TOP
    );
  }

  //タグ
  private static function GetForm() {
    return <<<EOF
<form method="post" action="game_play.php%s" target="bottom" name="reload_game"></form>
<form method="post" action="game_play.php%s" target="bottom" class="input-say" name="send" onSubmit="%sset_focus();">
<input type="hidden" name="token" value="%s">
<table><tr>
<td><textarea name="%s" rows="3" cols="70" wrap="soft"></textarea></td>
<td>
<input type="submit" onClick="setTimeout(&quot;auto_clear()&quot;, 10)" value="%s"><br>
<select name="%s">
<option value="%s">%s</option>
<option value="%s"%s>%s</option>
<option value="%s">%s</option>
<option value="%s">%s</option>
<option value="%s">%s</option>
</select><br>
[<a class="vote" href="game_vote.php%s">%s</a>]
<a class="top-link" href="./" target="_top">%s</a>
</td>
</tr></table>
</form>
EOF;
  }
}
