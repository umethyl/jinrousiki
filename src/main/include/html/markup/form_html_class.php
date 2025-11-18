<?php
//-- HTML 生成クラス (form 拡張) --//
final class FormHTML {
  //フッタ生成
  public static function GenerateFooter() {
    return HTML::GenerateTagFooter('form');
  }

  //共通実行ヘッダ生成
  public static function GenerateExecute($url, $str) {
    return Text::Format(self::GetExecute(), $url, $str);
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
  public static function OutputHeader($action, $method = 'post') {
    $str  = HTML::GenerateAttribute('method', $method);
    $str .= HTML::GenerateAttribute('action', $action);
    Text::Output(HTML::GenerateTagHeader('form' . $str));
  }

  //フッタ出力
  public static function OutputFooter() {
    Text::Output(self::GenerateFooter());
  }

  //共通実行ヘッダ出力
  public static function OutputExecute($url) {
    Text::Output(self::GenerateExecute($url, Message::FORM_EXECUTE));
  }

  //実行用 hidden 出力
  public static function OutputHiddenExecute() {
    Text::Output('<input type="hidden" name="execute" value="on">');
  }

  public static function OutputText($name, $size = 20, $placeholder = '') {
    $value = RQ::Get($name);
    Text::Printf(
      '<input type="text" name="%s" size="%d" placeholder="%s" value="%s">',
      $name, $size, $placeholder, $value
    );
  }

  //共通実行ヘッダタグ
  private static function GetExecute() {
    return <<<EOF
<form method="post" action="%s">
<input type="hidden" name="execute" value="on">
<input type="submit" value="%s">
EOF;
  }
}
