<?php
//-- HTML 生成クラス (トリップテスト拡張) --//
class TripTestHTML {
  //フォーム出力
  public static function OutputForm() {
    Text::Printf(self::GetForm(), TripTestMessage::KEY . Message::COLON);
  }

  //フォームタグ
  private static function GetForm() {
    return <<<EOF
<form method="post" action="trip_test.php">
<input type="hidden" name="execute" value="on">
<label for="trip">%s</label> <input type="text" id="trip" name="trip" size="20" value="">
</form>
EOF;
  }
}
