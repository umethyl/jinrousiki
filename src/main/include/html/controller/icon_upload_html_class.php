<?php
//-- HTML 生成クラス (IconUpload 拡張) --//
class IconUploadHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    self::OutputForm();
    self::OutputColor();
    self::OutputFooter();
  }

  //確認画面出力
  public static function OutputConfirm(array $list) {
    extract($list);
    Text::Printf(self::GetConfirm(),
      IconUploadMessage::MESSAGE,
      IconMessage::APPEARANCE, IconMessage::CATEGORY, IconMessage::AUTHOR,
      Icon::GetFile($file_name), $width, $height,
      $icon_no, $icon_name, $color, Message::SYMBOL, $color, $data,
      IconUploadMessage::CONFIRM,
      $icon_no, IconUploadMessage::CHECK_NG,
      $icon_no, IconUploadMessage::CHECK_OK
    );
  }

  //ヘッダ出力
  private static function OutputHeader() {
    Text::Printf(self::GetHeader(), IconMessage::TOP, IconMessage::UPLOAD, IconMessage::UPLOAD);
  }

  //フォーム出力
  private static function OutputForm() {
    $length = UserIcon::GetMaxLength(true);
    printf(self::GetForm(),
      IconUploadMessage::LINK,
      sprintf(IconUploadMessage::CAUTION,     UserIcon::GetSizeLimit()), UserIcon::GetCaution(),
      sprintf(IconUploadMessage::ICON_FORMAT, UserIcon::GetFileLimit()),
      IconUploadMessage::FILE_SELECT, UserIconConfig::FILE, IconUploadMessage::SUBMIT,
      IconMessage::NAME,	$length,
      IconMessage::APPEARANCE,	$length,
      IconMessage::CATEGORY,	$length,
      IconMessage::AUTHOR,	$length,
      IconMessage::COLOR,
      IconUploadMessage::FIX_COLOR, IconMessage::EXAMPLE
    );
  }

  //色情報出力
  private static function OutputColor() {
    $format = self::GetColor();

    $color_base = array();
    for ($i = 0; $i < 256; $i += 51) {
      $color_base[] = sprintf('%02X', $i);
    }

    $count = 0;
    foreach ($color_base as $i => $r) {
      foreach ($color_base as $j => $g) {
	foreach ($color_base as $k => $b) {
	  TableHTML::OutputFold($count++, 6);
	  $color = "#{$r}{$g}{$b}";
	  if (($i + $j + $k) < 8 && ($i + $j) < 5) {
	    $name = sprintf(self::GetColorName(), $color);
	  } else {
	    $name = $color;
	  }
	  Text::Printf($format, $color, $color, $color, $color, $name);
	}
      }
    }
  }

  //フッタ出力
  private static function OutputFooter() {
    Text::Output(self::GetFooter());
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<div class="link"><a href="./">%s</a></div>
<img class="title" src="img/title/icon_upload.jpg" title="%s" alt="%s">
EOF;
  }

  //フォームタグ
  private static function GetForm() {
    return <<<EOF
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
  }

  //色情報タグ
  private static function GetColor() {
    return <<<EOF
<td bgcolor="%s"><label for="%s">
<input type="radio" id="%s" name="color" value="%s">%s</label></td>
EOF;
  }

  //色名タグ
  private static function GetColorName() {
    return '<font color="#FFFFFF">%s</font>';
  }

  //フッタタグ
  private static function GetFooter() {
    return <<<EOF
</tr>
</table>
</td></tr></table></form></fieldset>
</td></tr></table>
EOF;
  }

  //確認画面タグ
  private static function GetConfirm() {
    return <<<EOF
<p>%s</p>
<p>[S] %s / [C] %s / [A] %s</p>
<table><tr>
<td><img src="%s" width="%d" height="%d"></td>
<td class="name">No. %d %s<br><font color="%s">%s</font>%s%s</td>
</tr>
<tr><td colspan="2">%s</td></tr>
<tr><td><form method="post" action="icon_upload.php">
  <input type="hidden" name="command" value="cancel">
  <input type="hidden" name="icon_no" value="%d">
  <input type="submit" value="%s">
</form></td>
<td><form method="post" action="icon_upload.php">
  <input type="hidden" name="command" value="success">
  <input type="hidden" name="icon_no" value="%d">
  <input type="submit" value="%s">
</form></td></tr></table>
EOF;
  }
}
