<?php
//-- HTML 生成クラス (Twitterテスト拡張) --//
final class TwitterTestHTML {
  //フォーム出力
  public static function OutputForm() {
    Text::Printf(self::GetForm(),
      GameMessage::ROOM_NUMBER_FOOTER,
      TwitterMessage::NAME,
      TwitterMessage::COMMENT,
      Message::FORM_EXECUTE
    );
  }

  //フォームタグ
  private static function GetForm() {
    return <<<EOF
<form method="post" action="twitter_test.php">
<input type="hidden" name="execute" value="on">
<table border="0">
<tr><td><label>%s</label></td><td><input type="text" name="number" size="5" value="1"></td></tr>
<tr><td><label>%s</label></td><td><input type="text" name="name" size="30" value=""></td></tr>
<tr><td><label>%s</label></td><td><input type="text" name="comment" size="30" value=""></td></tr>
<tr><td colspan="2"><input type="submit" value="%s"></td></tr>
</table>
</form>
EOF;
  }
}
