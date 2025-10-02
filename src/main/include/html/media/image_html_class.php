<?php
//-- HTML 生成クラス (画像拡張) --//
class ImageHTML {
  //画像生成
  public static function Generate($path, $css, $title) {
    return sprintf(self::Get(), $path, $css, $title);
  }

  //CSS 属性生成
  public static function GenerateCSS($class) {
    return sprintf(' class="%s"', $class);
  }

  //title 属性生成
  public static function GenerateTitle($alt, $title) {
    return sprintf(' alt="%s" title="%s"', $alt, $title);
  }

  //ユーザアイコン生成
  public static function GenerateIcon(User $user) {
    return sprintf(self::GetIcon(),
      Icon::GetFile($user->icon_filename), $user->color, Icon::GetTag()
    );
  }

  //ユーザアイコンサイズ属性生成
  public static function GenerateIconSize($width, $height) {
    return sprintf('width="%d" height="%d"', $width, $height);
  }

  //投票画面用アイコン出力
  public static function OutputVoteIcon(User $user, $path, $checkbox) {
    Text::Printf(self::GetVoteIcon(),
      $user->id, $path, $user->color, Icon::GetTag(), $user->color, $user->handle_name, $checkbox
    );
  }

  //画像タグ
  private static function Get() {
    return '<img src="%s"%s%s>';
  }

  //ユーザアイコンタグ
  private static function GetIcon() {
    return '<img src="%s" style="border-color: %s;" alt="" align="middle" %s>';
  }

  //投票画面用アイコンタグ
  private static function GetVoteIcon() {
    return <<<EOF
<td><label for="%d">
<img src="%s" style="border-color: %s;" %s>
<font color="%s">◆</font>%s<br>
%s</label></td>
EOF;
  }
}
