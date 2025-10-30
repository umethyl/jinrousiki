<?php
//-- HTML 生成クラス (form 拡張) --//
final class FormHTML {
  //ヘッダ生成
  public static function GenerateHeader($url, $str) {
    return Text::Format(self::GetHeader(), $url, $str);
  }

  //フッタ生成
  public static function GenerateFooter() {
    return HTML::GenerateTagFooter('form');
  }

  //チェック済み生成
  public static function Checked($flag) {
    return (true === $flag) ? HTML::GenerateAttribute('checked') : '';
  }

  //選択済み生成
  public static function Selected($flag) {
    return (true === $flag) ? HTML::GenerateAttribute('selected') : '';
  }

  //ヘッダ出力
  public static function OutputHeader($url) {
    Text::Output(self::GenerateHeader($url, Message::FORM_EXECUTE));
  }

  //フッタ出力
  public static function OutputFooter() {
    Text::Output(self::GenerateFooter());
  }

  //共通フォームヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<form method="post" action="%s">
<input type="hidden" name="execute" value="on">
<input type="submit" value="%s">
EOF;
  }
}
