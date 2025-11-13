<?php
//-- HTML 生成クラス --//
final class HTML {
  //共通タグ生成
  public static function GenerateTag($name, $str, $class = null, $id = null, $align = null) {
    $header = self::GenerateTagHeader($name, $class, $id, $align);
    $footer = self::GenerateTagFooter($name);
    return $header . $str . $footer;
  }

  //共通タグヘッダ生成
  public static function GenerateTagHeader($name, $class = null, $id = null, $align = null) {
    $str = $name;
    if (true === isset($id)) {
      $str .= self::GenerateAttribute('id', $id);
    }
    if (true === isset($class)) {
      $str .= self::GenerateAttribute('class', $class);
    }
    if (true === isset($align)) {
      $str .= self::GenerateAttribute('align', $align);
    }
    return Text::Quote($str, '<', '>');
  }

  //共通タグフッタ生成
  public static function GenerateTagFooter($name) {
    return Text::Quote($name, '</', '>');
  }

  //Attribute 要素生成
  public static function GenerateAttribute($name, $value = null) {
    $str = ' ' . $name;
    if (true === isset($value)) {
      $str .= Text::Quote($value, '="', '"');
    }
    return $str;
  }

  //共通 HTML ヘッダ生成
  public static function GenerateHeader($title, $css = null, $close = false) {
    $str = Text::Format(self::GetHeader(), ServerConfig::ENCODE, $title);
    if (null === $css) {
      $css = 'action';
    }
    $str .= self::LoadCSS(sprintf('%s/%s', JINROU_CSS, $css));
    if (true === $close) {
      $str .= self::GenerateBodyHeader();
    }
    return $str;
  }

  //共通 HTML フッタ生成
  public static function GenerateFooter() {
    return self::GetFooter();
  }

  //BODY ヘッダ生成
  public static function GenerateBodyHeader($css = null, $on_load = null) {
    return Text::Format(self::GetBodyHeader(),
      isset($css)     ? self::LoadCSS($css) : '',
      isset($on_load) ? self::GenerateAttribute('onLoad', $on_load) : ''
    );
  }

  //span 生成
  public static function GenerateSpan($str, $class = null, $id = null) {
    return self::GenerateTag('span', $str, $class, $id);
  }

  //窓を閉じてもらうメッセージを生成
  public static function GenerateCloseWindow($str) {
    return Text::Format(self::GetCloseWindow(), $str, Text::BR, Message::CLOSE_WINDOW);
  }

  //色付きメッセージ生成
  public static function GenerateMessage($str, $color, $css = null) {
    $style = '';
    if (true === isset($css)) {
      $style .= $css . '; ';
    }
    return sprintf(self::GetMessage(), $style, $color, $str);
  }

  //警告メッセージ生成
  public static function GenerateWarning($str) {
    return self::GenerateMessage($str, '#FF0000');
  }

  //CSS 読み込み
  public static function LoadCSS($path) {
    return Text::Format(self::GetCSS(), $path);
  }

  //共通 HTML ヘッダ出力
  public static function OutputHeader($title, $css = null, $close = false) {
    echo self::GenerateHeader($title, $css, $close);
  }

  //HTML フッタ出力
  public static function OutputFooter($exit = false) {
    DB::Disconnect();
    echo self::GenerateFooter();
    if (true === $exit) {
      exit;
    }
  }

  //CSS 出力
  public static function OutputCSS($path) {
    echo self::LoadCSS($path);
  }

  //HTML BODY ヘッダ出力
  public static function OutputBodyHeader($css = null, $on_load = null) {
    echo self::GenerateBodyHeader($css, $on_load);
  }

  //fieldset ヘッダ出力
  public static function OutputFieldsetHeader($str) {
    Text::Printf(self::GetFieldsetHeader(), $str);
  }

  //fieldset フッタ出力
  public static function OutputFieldsetFooter() {
    Text::Output(self::GenerateTagFooter('fieldset'));
  }

  //p 出力
  public static function OutputP($str) {
    Text::Output(self::GenerateTag('p', $str));
  }

  //警告メッセージ出力
  public static function OutputWarning($str) {
    Text::Output(self::GenerateWarning($str), true);
  }

  //結果ページ出力
  public static function OutputResult($title, $body, $url = null) {
    DB::Disconnect();
    self::OutputResultHeader($title, $url);
    Text::Output($body, true);
    self::OutputFooter(true);
  }

  //結果ページ HTML ヘッダ出力
  public static function OutputResultHeader($title, $url = null) {
    self::OutputHeader($title);
    if (true === isset($url)) {
      Text::Printf(self::GetJamp(), $url);
    }
    if (true === is_object(DB::$ROOM)) {
      DB::$ROOM->OutputCSS();
    }
    self::OutputBodyHeader();
  }

  //使用不可エラー出力
  public static function OutputUnusableError() {
    self::OutputResult(Message::DISABLE_ERROR, Message::UNUSABLE_ERROR);
  }

  //HTML ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="%s">
<title>%s</title>
EOF;
  }

  //HTML フッタタグ
  private static function GetFooter() {
    return <<<EOF
</body>
</html>
EOF;
  }

  //CSS 読み込みタグ
  private static function GetCSS() {
    return '<link rel="stylesheet" href="%s.css">';
  }

  //自動リロードタグ
  private static function GetJamp() {
    return '<meta http-equiv="Refresh" content="1;URL=%s">';
  }

  //BODY ヘッダタグ
  private static function GetBodyHeader() {
    return <<<EOF
%s</head>
<body%s>
EOF;
  }

  //フィールドセットヘッダタグ
  private static function GetFieldsetHeader() {
    return <<<EOF
<fieldset>
<legend>%s</legend>
EOF;
  }

  //窓を閉じてもらうメッセージタグ
  private static function GetCloseWindow() {
    return '%s%s<span style="color:#0000FF">%s</span>';
  }

  //メッセージタグ
  private static function GetMessage() {
    return '<span style="%scolor:%s;">%s</span>';
  }
}
