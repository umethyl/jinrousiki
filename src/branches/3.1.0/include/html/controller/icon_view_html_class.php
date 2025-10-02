<?php
//-- HTML 生成クラス (IconView 拡張) --//
class IconViewHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    IconHTML::Output();
    self::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(IconMessage::TITLE, 'icon_view');
    HTML::OutputJavaScript('submit_icon_search');
    HTML::OutputBodyHeader();
    Text::Printf(self::GetHeader(),
      IconMessage::TOP, IconMessage::UPLOAD, IconMessage::VIEW, IconMessage::VIEW
    );
  }

  //フッタ出力
  private static function OutputFooter() {
    echo TableHTML::GenerateTdFooter();
    TableHTML::OutputFooter();
    HTML::OutputFooter();
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<div class="link">
<a href="./">%s</a>
<a href="icon_upload.php">%s</a>
</div>
<img class="title" src="img/title/icon_view.jpg" title="%s" alt="%s">
<table align="center">
<tr><td>
EOF;
  }
}
