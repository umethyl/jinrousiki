<?php
//-- HTML 生成クラス (異議ありテスト拡張) --//
class ObjectionTestHTML {
  //フォーム出力
  public static function OutputForm(array $stack) {
    $url   = 'objection_test.php';
    $image = URL::Combine(JINROU_ROOT, GameConfig::OBJECTION_IMAGE);

    Text::Printf(HTML::GetP(), HTML::GenerateLink($url, ObjectionTestMessage::RESET));
    TableHTML::OutputHeader(null, false);
    foreach ($stack as $name) {
      TableHTML::OutputTrHeader();
      TableHTML::OutputTdHeader('objection');
      Text::Printf(self::GetForm(),
        $url, Switcher::ON, RequestDataTalk::OBJECTION, $name,
        $image . self::GetImage($name) . '.gif', ObjectionTestMessage::$$name
      );
      TableHTML::OutputTdFooter();
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
  }

  //画像取得
  private static function GetImage($name) {
    switch ($name) {
    case 'objection_male':
    case 'objection_female':
      return $name;

    default:
      return 'objection';
    }
  }

  //フォームタグ
  private static function GetForm() {
    return <<<EOF
<form method="post" action="%s">
<input type="hidden" name="execute" value="%s">
<input type="hidden" name="%s" value="%s">
<input type="image" name="objection_image" src="%s" border="0"> %s
</form>
EOF;
  }
}
