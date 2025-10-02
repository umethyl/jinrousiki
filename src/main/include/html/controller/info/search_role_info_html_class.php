<?php
//-- HTML 生成クラス (SearchRoleInfo 拡張) --//
class SearchRoleInfoHTML {
  //フォーム出力
  public static function OutputForm() {
    HTML::OutputFormHeader('search.php');
    self::OutputTextForm();
    HTML::OutputFormFooter();
  }

  //リンク出力
  public static function OutputLink($page, $role, $name) {
    Text::Printf(self::GetLink(), $page, $role, $name);
  }

  //フォーム入力欄出力
  private static function OutputTextForm() {
    Text::Printf(self::GetTextForm(), 'role', 'role', RQ::Get()->role);
  }

  //フォーム入力欄タグ
  private static function GetTextForm() {
    return '<input type="text" id="%s" name="%s" size="20" value="%s">';
  }

  //リンクタグ
  private static function GetLink() {
    return '<a href="%s.php#%s">%s</a>' . Text::BR;
  }
}
