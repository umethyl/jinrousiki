<?php
//-- アイコンアップロード処理クラス --//
class IconUpload {
  const URL = 'icon_upload.php';

  //実行処理
  static function Execute() {
    if (UserIconConfig::DISABLE) {
      HTML::OutputResult(IconUploadMessage::TITLE, IconUploadMessage::DISABLE);
    }
    isset(RQ::Get()->command) ? self::Upload() : self::Output();
  }

  //登録処理
  private static function Upload() {
    if (Security::CheckReferer(self::URL)) { //リファラチェック
      HTML::OutputResult(IconUploadMessage::TITLE, IconUploadMessage::REFERER);
    }

    switch (RQ::Get()->command) {
    case 'upload':
      break;

    case 'success': //セッション ID 情報を DB から削除
      self::UploadSuccess();
      break;

    case 'cancel': //アイコン削除
      self::UploadCancel();
      break;

    default:
      self::OutputResult(IconUploadMessage::COMMAND);
      break;
    }

    //アップロードされたファイルのエラーチェック
    if (@$_FILES['upfile']['error'][$i] != 0) {
      self::OutputResult(IconUploadMessage::FILE_UPLOAD . Text::BRLF . IconUploadMessage::RETRY);
    }
    extract(RQ::ToArray()); //引数を展開
    $back_url = self::GetURL();

    if ($icon_name == '') { //空白チェック
      self::OutputResult(IconUploadMessage::NAME);
    }
    UserIcon::CheckText(IconUploadMessage::TITLE, $back_url); //アイコン名の文字列長のチェック
    $color = UserIcon::CheckColor($color, IconUploadMessage::TITLE, $back_url); //色指定のチェック

    //ファイルサイズのチェック
    if ($size == 0) {
      self::OutputResult(IconUploadMessage::FILE_EMPTY);
    }
    if ($size > UserIconConfig::FILE) {
      self::OutputResult(IconUploadMessage::FILE_SIZE. UserIcon::GetFileLimit());
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
      self::OutputResult($type . IconUploadMessage::FILE_FORMAT);
      break;
    }

    //アイコンの高さと幅をチェック
    list($width, $height) = getimagesize($tmp_name);
    if ($width > UserIconConfig::WIDTH || $height > UserIconConfig::HEIGHT) {
      $str = sprintf(IconUploadMessage::SIZE_LIMIT, UserIcon::GetSizeLimit()) . Text::BRLF .
	sprintf(IconUploadMessage::UPLOAD_SIZE, $width, $height);
      self::OutputResult($str);
    }

    DB::Connect();
    if (! DB::Lock('icon')) { //トランザクション開始
      self::OutputResult(Message::DB_ERROR_LOAD);
    }

    if (IconDB::IsOver()) { //登録数上限チェック
      HTML::OutputResult(IconUploadMessage::TITLE, IconUploadMessage::OVER);
    }

    if (IconDB::ExistsName($icon_name)) { //アイコン名チェック
      self::OutputResult(sprintf(IconUploadMessage::DUPLICATE, $icon_name));
    }

    $icon_no = IconDB::GetNumber(); //次のアイコン番号取得
    if ($icon_no === false) self::OutputResult(Message::DB_ERROR_LOAD); //負荷エラー対策

    //ファイルをテンポラリからコピー
    $file_name = sprintf('%03s.%s', $icon_no, $ext); //ファイル名の桁を揃える
    if (! move_uploaded_file($tmp_name, Icon::GetFile($file_name))) {
      self::OutputResult(IconUploadMessage::FILE_COPY . Text::BRLF . IconUploadMessage::RETRY);
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
      self::OutputResult(Message::DB_ERROR_LOAD);
    }

    //確認ページを出力
    $format = <<<EOF
<p>%s</p>
<p>[S] %s / [C] %s / [A] %s</p>
<table><tr>
<td><img src="%s" width="%d" height="%d"></td>
<td class="name">No. %d %s<br><font color="%s">%s</font>%s%s</td>
</tr>
<tr><td colspan="2">%s</td></tr>
<tr><td><form method="post" action="icon_upload.php">
  <input type="hidden" name="command" value="cancel">
  <input type="hidden" name="icon_no" value="$icon_no">
  <input type="submit" value="%s">
</form></td>
<td><form method="post" action="icon_upload.php">
  <input type="hidden" name="command" value="success">
  <input type="hidden" name="icon_no" value="{$icon_no}">
  <input type="submit" value="%s">
</form></td></tr></table>
EOF;

    $title = IconUploadMessage::TITLE . IconUploadMessage::CHECK;
    HTML::OutputHeader($title, 'icon_upload_check', true);
    printf($format . Text::LF, IconUploadMessage::MESSAGE,
	   IconMessage::APPEARANCE, IconMessage::CATEGORY, IconMessage::AUTHOR,
	   Icon::GetFile($file_name), $width, $height,
	   $icon_no, $icon_name, $color, Message::SYMBOL, $color, $data,
	   IconUploadMessage::CONFIRM,
	   IconUploadMessage::CHECK_NG, IconUploadMessage::CHECK_OK);
    HTML::OutputFooter();
  }

  //アイコン登録完了処理
  private static function UploadSuccess() {
    $url = 'icon_view.php';
    $str = IconUploadMessage::SUCCESS . IconUploadMessage::JUMP_VIEW . Text::BRLF;

    DB::Connect();
    if (! IconDB::ClearSession(RQ::Get()->icon_no)) {
      $str .= IconUploadMessage::SESSION_DELETE . Text::BRLF;
    }
    HTML::OutputResult(IconUploadMessage::SUCCESS, $str . sprintf(Message::JUMP, $url), $url);
  }

  //アイコン削除処理
  private static function UploadCancel() {
     DB::Connect();
    if (! DB::Lock('icon')) { //トランザクション開始
      self::OutputResult(IconUploadMessage::DB_ERROR);
    }

    //アイコンのファイル名と登録時のセッション ID を取得
    $stack = IconDB::GetSession(RQ::Get()->icon_no);
    if (count($stack) < 1) { //アイコン情報取得エラー
      self::OutputResult(IconUploadMessage::DB_ERROR);
    }
    extract($stack);

    if ($session_id != Session::GetID()) { //セッション ID 確認
      self::OutputResult(IconUploadMessage::SESSION);
    }

    if (! IconDB::Delete(RQ::Get()->icon_no, $icon_filename)) { //削除処理
      self::OutputResult(IconUploadMessage::DB_ERROR);
    }
    DB::Disconnect();

    $url = 'icon_upload.php';
    $str = IconUploadMessage::DELETE . IconUploadMessage::JUMP_UPLOAD . Text::BRLF;
    HTML::OutputResult(IconUploadMessage::DELETE, $str . sprintf(Message::JUMP, $url), $url);
  }

  //アップロードフォーム出力
  private static function Output() {
    HTML::OutputHeader(IconUploadMessage::TITLE, 'icon_upload', true);
    self::OutputHeader();
    self::OutputForm();
    self::OutputColor();
    self::OutputFooter();
    HTML::OutputFooter();
  }

  //ヘッダー出力
  private static function OutputHeader() {
    $format = <<<EOF
<div class="link"><a href="./">%s</a></div>
<img class="title" src="img/title/icon_upload.jpg" title="%s" alt="%s">
EOF;
    printf($format . Text::LF, IconMessage::TOP, IconMessage::UPLOAD, IconMessage::UPLOAD);
  }

  //フォーム出力
  private static function OutputForm() {
    $format = <<<EOF
<table align="center">
<tr><td class="link"><a href="icon_view.php">%s</a></td></tr>
<tr><td class="caution">%s%s</td></tr>
<tr><td>
<fieldset><legend>%s</legend>
<form method="post" action="icon_upload.php" enctype="multipart/form-data">
<table>
<tr><td><label>%s</label></td>
<td>
<input type="file" name="file" size="80">
<input type="hidden" name="max_file_size" value="%s">
<input type="hidden" name="command" value="upload">
<input type="submit" value="%s">
</td></tr>
<tr><td><label>%s</label></td>
<td><input type="text" name="icon_name" %s</td></tr>
<tr><td><label>%s</label></td>
<td><input type="text" name="appearance" %s</td></tr>
<tr><td><label>%s</label></td>
<td><input type="text" name="category" %s</td></tr>
<tr><td><label>%s</label></td>
<td><input type="text" name="author" %s</td></tr>
<tr><td><label>%s</label></td>
<td>
<input id="fix_color" type="radio" name="color"><label for="fix_color">%s</label>
<input type="text" name="color" size="10px" maxlength="7">(%s)
</td></tr>
<tr><td colspan="2">
<table class="color" align="center">
<tr>
EOF;

    $length = UserIcon::GetMaxLength(true);
    printf($format, IconUploadMessage::LINK,
	   sprintf(IconUploadMessage::CAUTION, UserIcon::GetSizeLimit()), UserIcon::GetCaution(),
	   sprintf(IconUploadMessage::ICON_FORMAT, UserIcon::GetFileLimit()),
	   IconUploadMessage::FILE_SELECT, UserIconConfig::FILE, IconUploadMessage::SUBMIT,
	   IconMessage::NAME, $length,
	   IconMessage::APPEARANCE, $length,
	   IconMessage::CATEGORY, $length,
	   IconMessage::AUTHOR, $length,
	   IconMessage::COLOR, IconUploadMessage::FIX_COLOR, IconMessage::EXAMPLE);
  }

  //色情報出力
  private static function OutputColor() {
    $format = <<<EOF
<td bgcolor="%s"><label for="%s">
<input type="radio" id="%s" name="color" value="%s">%s</label></td>
EOF;

    $color_base = array();
    for ($i = 0; $i < 256; $i += 51) {
      $color_base[] = sprintf('%02X', $i);
    }

    $count = 0;
    foreach ($color_base as $i => $r) {
      foreach ($color_base as $j => $g) {
	foreach ($color_base as $k => $b) {
	  if ($count > 0 && $count % 6 == 0) Text::Output(Text::TR); //6個ごとに改行

	  $color = "#{$r}{$g}{$b}";
	  if (($i + $j + $k) < 8 && ($i + $j) < 5) {
	    $name = sprintf('<font color="#FFFFFF">%s</font>', $color);
	  } else {
	    $name = $color;
	  }
	  printf($format . Text::LF, $color, $color, $color, $color, $name);
	  $count++;
	}
      }
    }
  }

  //フッター出力
  private static function OutputFooter() {
    echo <<<EOF
</tr>
</table>
</td></tr></table></form></fieldset>
</td></tr></table>

EOF;
  }

  //バックリンク取得
  private static function GetURL($return = false) {
    $url = sprintf('<a href="%s">%s</a>', self::URL, Message::BACK);
    return $return ? Text::BRLF . $url : $url;
  }

  //エラー処理
  private static function OutputResult($str) {
    HTML::OutputResult(IconUploadMessage::TITLE, $str . self::GetURL(true));
  }
}
