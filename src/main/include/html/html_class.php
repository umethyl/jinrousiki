<?php
//-- HTML 生成クラス --//
class HTML {
  //共通タグヘッダ生成
  public static function GenerateTagHeader($name, $class = null, $id = null, $align = null) {
    $tag = '<' . $name;
    if (isset($id))    $tag .= ' id="'    . $id    . '"';
    if (isset($class)) $tag .= ' class="' . $class . '"';
    if (isset($align)) $tag .= ' align="' . $align . '"';
    return $tag . '>';
  }

  //共通タグフッタ生成
  public static function GenerateTagFooter($name) {
    return '</' . $name . '>';
  }

  //共通 HTML ヘッダ生成
  public static function GenerateHeader($title, $css = null, $close = false) {
    $str = Text::Format(self::GetHeader(), ServerConfig::ENCODE, $title);
    if (is_null($css)) $css = 'action';
    $str .= self::LoadCSS(sprintf('%s/%s', JINROU_CSS, $css));
    if ($close) $str .= self::GenerateBodyHeader();
    return $str;
  }

  //JavaScript ヘッダ生成
  public static function GenerateJavaScriptHeader() {
    return Text::Add(self::GetJavaScriptHeader());
  }

  //JavaScript フッタ生成
  public static function GenerateJavaScriptFooter() {
    return Text::Add(self::GetJavaScriptFooter());
  }

  //ページジャンプ用 JavaScript 生成
  public static function GenerateSetLocation() {
    $str = Text::Add('if (top != self) { top.location.href = self.location.href; }');
    return self::GenerateJavaScriptHeader() . $str . self::GenerateJavaScriptFooter();
  }

  //BODY ヘッダ生成
  public static function GenerateBodyHeader($css = null, $on_load = null) {
    return Text::Format(self::GetBodyHeader(),
      isset($css) ? self::LoadCSS($css) : '',
      isset($on_load) ? ' onLoad="' . $on_load . '"' : ''
    );
  }

  //div 生成
  public static function GenerateDiv($str, $class = null, $id = null) {
    return self::GenerateDivHeader($class, $id) . $str . self::GenerateDivFooter();
  }

  //div ヘッダ生成
  public static function GenerateDivHeader($class = null, $id = null) {
    return self::GenerateTagHeader('div', $class, $id);
  }

  //div フッタ生成
  public static function GenerateDivFooter($return = false) {
    return self::GenerateTagFooter('div') . ($return ? Text::LF : '');
  }

  //span 生成
  public static function GenerateSpan($str, $class = null, $id = null) {
    return self::GenerateTagHeader('span', $class, $id) . $str . self::GenerateTagFooter('span');
  }

  //リンク生成
  public static function GenerateLink($url, $str) {
    return sprintf(self::GetLink(), $url, $str);
  }

  //ログへのリンク生成
  public static function GenerateLogLink($url, $watch = false, $header = '', $css = '', $footer = '') {
    $str = sprintf(self::GetLogLink(), $header,
      $url, $css, Message::LOG_NORMAL,
      $url, $css, Message::LOG_REVERSE,
      $url, $css, Message::LOG_DEAD,
      $url, $css, Message::LOG_DEAD_REVERSE,
      $url, $css, Message::LOG_HEAVEN,
      $url, $css, Message::LOG_HEAVEN_REVERSE
    );

    if ($watch) {
      $str .= sprintf(Text::LF . self::GetWatchLogLink(),
	$url, $css, Message::LOG_WATCH,
	$url, $css, Message::LOG_WATCH_REVERSE
      );
    }
    return $str . $footer;
  }

  //共通フォームヘッダ生成
  public static function GenerateFormHeader($url, $str) {
    return Text::Format(self::GetFormHeader(), $url, $str);
  }

  //共通フォームフッタ生成
  public static function GenerateFormFooter() {
    return self::GenerateTagFooter('form');
  }

  //チェック済み生成
  public static function GenerateChecked($flag) {
    return $flag ? ' checked' : '';
  }

  //選択済み生成
  public static function GenerateSelected($flag) {
    return $flag ? ' selected' : '';
  }

  //窓を閉じるボタン生成
  public static function GenerateCloseWindow($str) {
    return Text::Format(self::GetCloseWindow(), $str, Text::BR, Message::CLOSE_WINDOW);
  }

  //警告メッセージ生成
  public static function GenerateWarning($str) {
    return sprintf(self::GetWarning(), $str);
  }

  //CSS 読み込み
  public static function LoadCSS($path) {
    return Text::Format(self::GetCSS(), $path);
  }

  //JavaScript 読み込み
  public static function LoadJavaScript($file, $path = null) {
    if (is_null($path)) $path = JINROU_ROOT . '/javascript';
    return Text::Format(self::GetJavaScript(), $path, $file);
  }

  //共通 HTML ヘッダ出力
  public static function OutputHeader($title, $css = null, $close = false) {
    echo self::GenerateHeader($title, $css, $close);
  }

  //HTML フッタ出力
  public static function OutputFooter($exit = false) {
    DB::Disconnect();
    echo self::GetFooter();
    if ($exit) exit;
  }

  //CSS 出力
  public static function OutputCSS($path) {
    echo self::LoadCSS($path);
  }

  //JavaScript 出力
  public static function OutputJavaScript($file, $path = null) {
    echo self::LoadJavaScript($file, $path);
  }

  //HTML BODY ヘッダ出力
  public static function OutputBodyHeader($css = null, $on_load = null) {
    echo self::GenerateBodyHeader($css, $on_load);
  }

  //フレーム HTML ヘッダ出力
  public static function OutputFrameHeader($title) {
    Text::Printf(self::GetFrameHeader(), ServerConfig::ENCODE, $title);
  }

  //フレーム HTML フッタ出力
  public static function OutputFrameFooter() {
    printf(self::GetFrameFooter(), Message::NO_FRAME);
  }

  //ヘッダタイトル出力
  public static function OutputHeaderTitle($str) {
    Text::Printf(self::GetHeaderTitle(), $str);
  }

  //fieldset ヘッダ出力
  public static function OutputFieldsetHeader($str) {
    Text::Printf(self::GetFieldsetHeader(), $str);
  }

  //fieldset フッタ出力
  public static function OutputFieldsetFooter() {
    Text::Output(self::GenerateTagFooter('fieldset'));
  }

  //フォームヘッダ出力
  public static function OutputFormHeader($url) {
    Text::Output(self::GenerateFormHeader($url, Message::FORM_EXECUTE));
  }

  //フォームフッタ出力
  public static function OutputFormFooter() {
    Text::Output(self::GenerateFormFooter());
  }

  //div 出力
  public static function OutputDiv($str, $class = null, $id = null) {
    Text::Output(self::GenerateDiv($str, $class, $id));
  }

  //div ヘッダ出力
  public static function OutputDivHeader($class = null, $id = null) {
    Text::Output(self::GenerateDivHeader($class, $id));
  }

  //div フッタ出力
  public static function OutputDivFooter($return = false) {
    Text::Output(self::GenerateDivFooter($return));
  }

  //リンク出力
  public static function OutputLink($url, $str, $line = false) {
    Text::Output(self::GenerateLink($url, $str), $line);
  }

  //警告メッセージ出力
  public static function OutputWarning($str) {
    Text::Output(self::GenerateWarning($str), true);
  }

  //結果ページ出力
  public static function OutputResult($title, $body, $url = '') {
    DB::Disconnect();
    self::OutputResultHeader($title, $url);
    Text::Output($body, true);
    self::OutputFooter(true);
  }

  //結果ページ HTML ヘッダ出力
  public static function OutputResultHeader($title, $url = '') {
    self::OutputHeader($title);
    if ($url != '') Text::Printf(self::GetJamp(), $url);
    if (is_object(DB::$ROOM)) echo DB::$ROOM->GenerateCSS();
    self::OutputBodyHeader();
  }

  //使用不可エラー出力
  public static function OutputUnusableError() {
    self::OutputResult(Message::DISABLE_ERROR, Message::UNUSABLE_ERROR);
  }

  //p タグ
  public static function GetP() {
    return '<p>%s</p>';
  }

  //HTML ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
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

  //JavaScript 読み込みタグ
  private static function GetJavaScript() {
    return '<script type="text/javascript" src="%s/%s.js"></script>';
  }

  //JavaScript ヘッダタグ
  private static function GetJavaScriptHeader() {
    return '<script type="text/javascript"><!--';
  }

  //JavaScript フッタタグ
  private static function GetJavaScriptFooter() {
    return '//--></script>';
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

  //フレーム HTML ヘッダタグ
  private static function GetFrameHeader() {
    return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s">
<title>%s</title>
</head>
EOF;
  }

  //フレーム HTML ヘッダタグ
  private static function GetFrameFooter() {
    return <<<EOF
<noframes>
<body>
%s
</body>
</noframes>
</frameset>
</html>
EOF;
  }

  //ヘッダタイトルタグ
  private static function GetHeaderTitle() {
    return '<h1>%s</h1>';
  }

  //フィールドセットヘッダタグ
  private static function GetFieldsetHeader() {
    return <<<EOF
<fieldset>
<legend>%s</legend>
EOF;
  }

  //共通フォームヘッダタグ
  private static function GetFormHeader() {
    return <<<EOF
<form method="post" action="%s">
<input type="hidden" name="execute" value="on">
<input type="submit" value="%s">
EOF;
  }

  //窓を閉じるボタンタグ
  private static function GetCloseWindow() {
    return <<<EOF
%s%s
<form method="post" action="#">
<input type="button" value="%s" onClick="window.close()">
</form>
EOF;
  }

  //リンクタグ
  private static function GetLink() {
    return '<a href="%s">%s</a>';
  }

  //ログへのリンクタグ
  private static function GetLogLink() {
    return <<<EOF
%s <a target="_top" href="%s"%s>%s</a>
<a target="_top" href="%s&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&heaven_talk=on"%s>%s</a>
<a target="_top" href="%s&heaven_talk=on&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&heaven_only=on"%s >%s</a>
<a target="_top" href="%s&heaven_only=on&reverse_log=on"%s>%s</a>
EOF;
  }

  //ログへのリンクタグ (観戦モード用)
  private static function GetWatchLogLink() {
    return <<<EOF
<a target="_top" href="%s&watch=on"%s>%s</a>
<a target="_top" href="%s&watch=on&reverse_log=on"%s>%s</a>
EOF;
  }

  //警告メッセージタグ
  private static function GetWarning() {
    return '<font color="#FF0000">%s</font>';
  }
}
