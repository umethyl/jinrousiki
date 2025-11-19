<?php
//-- HTML 生成クラス (IconView 拡張) --//
final class IconViewHTML {
  //出力
  public static function Output() {
    self::OutputHeader();
    self::OutputIcon();
    self::OutputFooter();
  }

  //ヘッダ出力
  private static function OutputHeader() {
    HTML::OutputHeader(IconMessage::TITLE, 'icon_view');
    JavaScriptHTML::Output('submit_icon_search');
    HTML::OutputBodyHeader();
    Text::Printf(self::GetHeader(),
      IconMessage::TOP, IconMessage::UPLOAD, IconMessage::VIEW, IconMessage::VIEW
    );
  }

  //アイコン情報出力
  private static function OutputIcon() {
    if (RQ::Fetch()->icon_no > 0) {
      self::OutputSingleEdit();
    } elseif (RQ::Fetch()->Enable(RequestDataIcon::MULTI)) {
      self::OutputMultiEdit();
    } else {
      HTML::OutputFieldsetHeader(IconMessage::TITLE);
      IconHTML::Output('icon_view');
    }
    HTML::OutputFieldsetFooter();
  }

  //個別編集ページ出力
  private static function OutputSingleEdit() {
    self::OutputLink();
    HTML::OutputFieldsetHeader(IconMessage::EDIT);
    self::OutputSingleEditForm();
  }

  //一括編集ページ出力
  private static function OutputMultiEdit() {
    self::OutputLink();
    HTML::OutputFieldsetHeader(IconMessage::EDIT . Text::Quote(IconMessage::MULTI_EDIT));
    self::OutputMultiEditForm();
  }

  //バックリンク出力
  private static function OutputLink() {
    DivHTML::Output(LinkHTML::Generate('icon_view.php', IconMessage::BACK), [HTML::CSS => 'link']);
  }

  //個別編集フォーム出力
  private static function OutputSingleEditForm() {
    $icon_no = RQ::Fetch()->icon_no;
    $stack = IconDB::Get($icon_no);
    if (count($stack) < 1) {
      return;
    }

    extract($stack);
    $size = UserIcon::GetMaxLength();
    Text::Printf(self::GetSingleEdit(),
      $icon_no, Icon::GetFile($icon_filename), $icon_name, $color,
      IconMessage::NAME,	$icon_name,	$size,
      IconMessage::APPEARANCE,	$appearance,	$size,
      IconMessage::CATEGORY,	$category,	$size,
      IconMessage::AUTHOR,	$author,	$size,
      IconMessage::COLOR,	$color,		IconMessage::EXAMPLE,
      IconMessage::DISABLE, FormHTML::Checked($disable > 0),
      IconMessage::PASSWORD,
      IconMessage::SUBMIT
    );
  }

  //一括編集フォーム出力
  private static function OutputMultiEditForm() {
    $size = UserIcon::GetMaxLength();
    Text::Printf(self::GetMultiEdit(),
      IconMessage::NUMBER_FORMAT,
      IconMessage::NUMBER,	UserIconConfig::LENGTH,
      IconMessage::APPEARANCE,	$size,
      IconMessage::CATEGORY,	$size,
      IconMessage::AUTHOR,	$size,
      IconMessage::COLOR,	IconMessage::EXAMPLE,
      IconMessage::DISABLE,
      IconMessage::PASSWORD,
      IconMessage::SUBMIT
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
<img src="img/title/icon_view.jpg" alt="%s" title="%s" class="title">
<table align="center">
<tr><td>
EOF;
  }

  //個別編集フォームタグ
  private static function GetSingleEdit() {
    return <<<EOF
<form method="post" action="icon_edit.php">
<input type="hidden" name="icon_no" value="%d">
<table cellpadding="3">
<tr>
  <td rowspan="7"><img src="%s" alt="%s" style="border:3px solid %s;"></td>
  <td><label for="name">%s</label></td>
  <td><input type="text" id="name" name="icon_name" value="%s" %s></td>
</tr>
<tr>
  <td><label for="appearance">%s</label></td>
  <td><input type="text" id="appearance" name="appearance" value="%s" %s></td>
</tr>
<tr>
  <td><label for="category">%s</label></td>
  <td><input type="text" id="category" name="category" value="%s" %s></td>
</tr>
<tr>
  <td><label for="author">%s</label></td>
  <td><input type="text" id="author" name="author" value="%s" %s></td>
</tr>
<tr>
  <td><label for="color">%s</label></td>
  <td><input type="text" id="color" name="color" size="10px" maxlength="7" value="%s"> (%s)</td>
</tr>
<tr>
  <td><label for="disable">%s</label></td>
  <td><input type="checkbox" id="disable" name="disable" value="on"%s></td>
</tr>
<tr>
  <td><label for="password">%s</label></td>
  <td><input type="password" id="password" name="password" size="20" value=""></td>
</tr>
<tr>
  <td colspan="3"><input type="submit" value="%s"></td>
</tr>
</table>
</form>
EOF;
  }


  //アイコン一括編集フォームタグ
  private static function GetMultiEdit() {
    return <<<EOF
<form method="post" action="icon_edit.php">
<input type="hidden" name="multi_edit" value="on">
<table cellpadding="2">
<caption>%s</caption>
<tr>
  <td><label for="number_list">%s</label></td>
  <td><input type="text" id="number_list" name="number_list" value="" size="%d"></td>
</tr>
<tr>
  <td><label for="appearance">%s</label></td>
  <td><input type="text" id="appearance" name="appearance" value="" %s></td>
</tr>
<tr>
  <td><label for="category">%s</label></td>
  <td><input type="text" id="category" name="category" value="" %s></td>
</tr>
<tr>
  <td><label for="author">%s</label></td>
  <td><input type="text" id="author" name="author" value="" %s></td>
</tr>
<tr>
  <td><label for="color">%s</label></td>
  <td><input type="text" id="color" name="color" size="10px" maxlength="7" value=""> (%s)</td>
</tr>
<tr>
  <td><label for="disable">%s</label></td>
  <td><input type="checkbox" id="disable" name="disable" value="on"></td>
</tr>
<tr>
  <td><label for="password">%s</label></td>
  <td><input type="password" id="password" name="password" size="20" value=""></td>
</tr>
<tr>
  <td colspan="2"><input type="submit" value="%s"></td>
</tr>
</table>
</form>
EOF;
  }
}
