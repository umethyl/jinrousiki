<?php
//-- アイコン表示出力クラス --//
class IconView {
  static function Output() {
    DB::Connect();
    Session::Start();

    HTML::OutputHeader('ユーザアイコン一覧', 'icon_view');
    HTML::OutputJavaScript('submit_icon_search');
    HTML::OutputBodyHeader();
    echo <<<EOF
<div class="link">
<a href="./">←TOP</a>
<a href="icon_upload.php">アイコン登録</a>
</div>
<img class="title" src="img/icon_view_title.jpg" title="アイコン一覧" alt="アイコン一覧">
<table align="center">
<tr><td>

EOF;
    IconHTML::Output();
    Text::Output('</td></tr></table>');
    HTML::OutputFooter();
  }
}
