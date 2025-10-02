<?php
//-- アイコン表示出力クラス --//
class IconView {
  //実行
  static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    DB::Connect();
    Session::Start();
  }

  //出力
  private static function Output() {
    HTML::OutputHeader(IconMessage::TITLE, 'icon_view');
    HTML::OutputJavaScript('submit_icon_search');
    HTML::OutputBodyHeader();
    self::OutputHeader();
    IconHTML::Output();
    Text::Output('</td></tr></table>');
    HTML::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    $format = <<<EOF
<div class="link">
<a href="./">%s</a>
<a href="icon_upload.php">%s</a>
</div>
<img class="title" src="img/title/icon_view.jpg" title="%s" alt="%s">
<table align="center">
<tr><td>
EOF;

    printf($format . Text::LF,
	   IconMessage::TOP, IconMessage::UPLOAD, IconMessage::VIEW, IconMessage::VIEW);
  }
}
