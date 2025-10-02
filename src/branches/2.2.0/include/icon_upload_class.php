<?php
//-- アイコンアップロード処理クラス --//
class IconUpload {
  const TITLE = 'アイコン登録エラー';
  const URL   = "<br>\n<a href=\"icon_upload.php\">戻る</a>";

  //実行処理
  static function Execute() {
    if (UserIconConfig::DISABLE) {
      HTML::OutputResult('ユーザアイコンアップロード', '現在アップロードは停止しています');
    }
    Loader::LoadRequest('RequestIconUpload');
    isset(RQ::Get()->command) ? self::Upload() : self::Output();
  }

  //投稿処理
  private static function Upload() {
    if (Security::CheckReferer('icon_upload.php')) { //リファラチェック
      HTML::OutputResult('ユーザアイコンアップロード', '無効なアクセスです');
    }

    switch (RQ::Get()->command) {
    case 'upload':
      break;

    case 'success': //セッション ID 情報を DB から削除
      $url = 'icon_view.php';
      $str = '登録完了：アイコン一覧のページに飛びます。' . Text::BRLF .
	'切り替わらないなら <a href="%s">ここ</a> 。';
      DB::Connect();
      if (! IconDB::ClearSession(RQ::Get()->icon_no)) {
	$str .= Text::BRLF . 'セッションの削除に失敗しました。';
      }
      HTML::OutputResult('アイコン登録完了', sprintf($str, $url), $url);
      break;

    case 'cancel': //アイコン削除
      //負荷エラー用
      $str = 'サーバが混雑しているため、削除に失敗しました。' . Text::BRLF .
	'管理者に問い合わせてください。';

      //トランザクション開始
      DB::Connect();
      if (! DB::Lock('icon')) HTML::OutputResult(self::TITLE, $str . self::URL);

      //アイコンのファイル名と登録時のセッション ID を取得
      $stack = IconDB::GetSession(RQ::Get()->icon_no);
      if (count($stack) < 1) HTML::OutputResult(self::TITLE, $str . self::URL);
      extract($stack);

      if ($session_id != Session::GetID()) { //セッション ID 確認
	$str = '削除失敗：アップロードセッションが一致しません';
	HTML::OutputResult('アイコン削除失敗', $str . self::URL);
      }

      if (! IconDB::Delete(RQ::Get()->icon_no, $icon_filename)) { //削除処理
	HTML::OutputResult(self::TITLE, $str . self::URL);
      }
      DB::Disconnect();

      $url = 'icon_upload.php';
      $str = '削除完了：登録ページに飛びます。' . Text::BRLF .
	'切り替わらないなら <a href="%s">ここ</a> 。';
      HTML::OutputResult('アイコン削除完了', sprintf($str, $url), $url);
      break;

    default:
      HTML::OutputResult(self::TITLE, '無効なコマンドです' . self::URL);
      break;
    }

    //アップロードされたファイルのエラーチェック
    if (@$_FILES['upfile']['error'][$i] != 0) {
      $str = 'ファイルのアップロードエラーが発生しました。' . Text::BRLF .
	'再度実行してください。';
      HTML::OutputResult(self::TITLE, $str . self::URL);
    }
    extract(RQ::ToArray()); //引数を展開

    //空白チェック
    if ($icon_name == '') {
      HTML::OutputResult(self::TITLE, 'アイコン名を入力してください' . self::URL);
    }
    UserIcon::CheckText(self::TITLE, self::URL); //アイコン名の文字列長のチェック
    $color = UserIcon::CheckColor($color, self::TITLE, self::URL); //色指定のチェック

    //ファイルサイズのチェック
    if ($size == 0) HTML::OutputResult(self::TITLE, 'ファイルが空です' . self::URL);
    if ($size > UserIconConfig::FILE) {
      HTML::OutputResult(self::TITLE, 'ファイルサイズは ' . UserIcon::GetFileLimit() . self::URL);
    }

    //ファイルの種類のチェック
    switch ($type) {
    case 'image/jpeg':
    case 'image/pjpeg':
      $ext = 'jpg';
      break;

    case 'image/gif':
      $ext = 'gif';
      break;

    case 'image/png':
    case 'image/x-png':
      $ext = 'png';
      break;

    default:
      $str = $type . ' : jpg、gif、png 以外のファイルは登録できません';
      HTML::OutputResult(self::TITLE, $str . self::URL);
      break;
    }

    //アイコンの高さと幅をチェック
    list($width, $height) = getimagesize($tmp_name);
    if ($width > UserIconConfig::WIDTH || $height > UserIconConfig::HEIGHT) {
      $format = 'アイコンは %s しか登録できません。<br>'."\n" .
	'送信されたファイル → <span class="color">幅 %d、高さ %d</span>';
      $str = sprintf($format, UserIcon::GetSizeLimit(), $width, $height);
      HTML::OutputResult(self::TITLE, $str . self::URL);
    }

    //負荷エラー用
    $str = "サーバが混雑しています。<br>\n時間を置いてから再登録をお願いします。" . self::URL;

    DB::Connect();
    if (! DB::Lock('icon')) HTML::OutputResult(self::TITLE, $str); //トランザクション開始

    //登録数上限チェック
    if (IconDB::IsOver()) HTML::OutputResult(self::TITLE, 'これ以上登録できません');

    //アイコン名チェック
    if (IconDB::ExistsName($icon_name)) {
      $str = sprintf('アイコン名 "%s" は既に登録されています', $icon_name);
      HTML::OutputResult(self::TITLE, $str . self::URL);
    }

    $icon_no = IconDB::GetNumber(); //次のアイコン番号取得
    if ($icon_no === false) HTML::OutputResult(self::TITLE, $str); //負荷エラー対策

    //ファイルをテンポラリからコピー
    $file_name = sprintf('%03s.%s', $icon_no, $ext); //ファイル名の桁を揃える
    if (! move_uploaded_file($tmp_name, Icon::GetFile($file_name))) {
      $str = "ファイルのコピーに失敗しました。<br>\n再度実行してください。";
      HTML::OutputResult(self::TITLE, $str . self::URL);
    }

    //データベースに登録
    $data = '';
    $session_id = Session::Reset(); //セッション ID を取得
    $items  = 'icon_no, icon_name, icon_filename, icon_width, icon_height, color, ' .
      'session_id, regist_date';
    $values = "{$icon_no}, '{$icon_name}', '{$file_name}', {$width}, {$height}, '{$color}', " .
      "'{$session_id}', NOW()";

    if ($appearance != '') {
      $data   .= '<br>[S]' . $appearance;
      $items  .= ', appearance';
      $values .= ", '{$appearance}'";
    }
    if ($category != '') {
      $data   .= '<br>[C]' . $category;
      $items  .= ', category';
      $values .= ", '{$category}'";
    }
    if ($author != '') {
      $data   .= '<br>[A]' . $author;
      $items  .= ', author';
      $values .= ", '{$author}'";
    }

    if (DB::Insert('user_icon', $items, $values)) {
      DB::Commit();
      DB::Disconnect();
    }
    else {
      HTML::OutputResult(self::TITLE, $str);
    }

    //確認ページを出力
    HTML::OutputHeader('ユーザアイコンアップロード処理[確認]', 'icon_upload_check', true);
    $path = Icon::GetFile($file_name);
    echo <<<EOF
<p>ファイルをアップロードしました。<br>今だけやりなおしできます</p>
<p>[S] 出典 / [C] カテゴリ / [A] アイコンの作者</p>
<table><tr>
<td><img src="{$path}" width="{$width}" height="{$height}"></td>
<td class="name">No. {$icon_no} {$icon_name}<br><font color="{$color}">◆</font>{$color}{$data}</td>
</tr>
<tr><td colspan="2">よろしいですか？</td></tr>
<tr><td><form method="post" action="icon_upload.php">
  <input type="hidden" name="command" value="cancel">
  <input type="hidden" name="icon_no" value="$icon_no">
  <input type="submit" value="やりなおし">
</form></td>
<td><form method="post" action="icon_upload.php">
  <input type="hidden" name="command" value="success">
  <input type="hidden" name="icon_no" value="{$icon_no}">
  <input type="submit" value="登録完了">
</form></td></tr></table>

EOF;
    HTML::OutputFooter();
  }

  //アップロードフォーム出力
  private static function Output() {
    HTML::OutputHeader('ユーザアイコンアップロード', 'icon_upload', true);
    $file      = UserIcon::GetFileLimit();
    $length    = UserIcon::GetMaxLength(true);
    $size      = UserIcon::GetSizeLimit();
    $caution   = UserIcon::GetCaution();
    $file_size = UserIconConfig::FILE;
    echo <<<EOF
<div class="link"><a href="./">←TOP</a></div>
<img class="title" src="img/icon_upload_title.jpg" title="アイコン登録" alt="アイコン登録">
<table align="center">
<tr><td class="link"><a href="icon_view.php">→アイコン一覧</a></td></tr>
<tr><td class="caution">＊あらかじめ指定する大きさ ({$size}) にリサイズしてからアップロードしてください。{$caution}</td></tr>
<tr><td>
<fieldset><legend>アイコン指定 (jpg / gif / png 形式で登録して下さい。{$file})</legend>
<form method="post" action="icon_upload.php" enctype="multipart/form-data">
<table>
<tr><td><label>ファイル選択</label></td>
<td>
<input type="file" name="file" size="80">
<input type="hidden" name="max_file_size" value="{$file_size}">
<input type="hidden" name="command" value="upload">
<input type="submit" value="登録">
</td></tr>
<tr><td><label>アイコンの名前</label></td>
<td><input type="text" name="icon_name" {$length}</td></tr>
<tr><td><label>出典</label></td>
<td><input type="text" name="appearance" {$length}</td></tr>
<tr><td><label>カテゴリ</label></td>
<td><input type="text" name="category" {$length}</td></tr>
<tr><td><label>アイコンの作者</label></td>
<td><input type="text" name="author" {$length}</td></tr>
<tr><td><label>アイコン枠の色</label></td>
<td>
<input id="fix_color" type="radio" name="color"><label for="fix_color">手入力</label>
<input type="text" name="color" size="10px" maxlength="7">(例：#6699CC)
</td></tr>
<tr><td colspan="2">
<table class="color" align="center">
<tr>

EOF;

    $count  = 0;
    $format = '<td bgcolor="%s"><label for="%s">' .
      '<input type="radio" id="%s" name="color" value="%s">%s</label></td>' . Text::LF;
    $color_base = array();
    for ($i = 0; $i < 256; $i += 51) $color_base[] = sprintf('%02X', $i);
    foreach ($color_base as $i => $r) {
      foreach ($color_base as $j => $g) {
	foreach ($color_base as $k => $b) {
	  if ($count > 0 && $count % 6 == 0) Text::Output(Text::TR); //6個ごとに改行
	  $color = "#{$r}{$g}{$b}";
	  $name  = ($i + $j + $k) < 8  && ($i + $j) < 5 ?
	    sprintf('<font color="#FFFFFF">%s</font>', $color) : $color;
	  printf($format, $color, $color, $color, $color, $name);
	  $count++;
	}
      }
    }

    echo <<<EOF
</tr>
</table>
</td></tr></table></form></fieldset>
</td></tr></table>

EOF;
    HTML::OutputFooter();
  }
}
