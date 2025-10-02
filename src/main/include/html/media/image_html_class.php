<?php
//-- HTML 生成クラス (画像拡張) --//
class ImageHTML {
  //画像生成
  public static function Generate($path, $title, $css) {
    return sprintf(self::Get(), $path, $title, $css);
  }

  //alt, title 属性生成
  public static function GenerateTitle($alt, $title) {
    return HTML::GenerateAttribute('alt', $alt) . HTML::GenerateAttribute('title', $title);
  }

  //CSS 属性生成
  public static function GenerateCSS($class) {
    return HTML::GenerateAttribute('class', $class);
  }

  //ユーザアイコン生成
  public static function GenerateIcon(User $user) {
    return sprintf(self::GetIcon(),
      Icon::GetFile($user->icon_filename), $user->color, Icon::GetSize()
    );
  }

  //ユーザアイコンサイズ属性生成
  public static function GenerateIconSize($width, $height) {
    return HTML::GenerateAttribute('width', $width) . HTML::GenerateAttribute('height', $height);
  }

  //投票画面用アイコン出力
  public static function OutputVoteIcon(User $user, $path, $checkbox) {
    Text::Printf(self::GetVoteIcon(),
      $user->id, $path, $user->color, Icon::GetSize(), $user->color, Message::SYMBOL,
      $user->handle_name, $checkbox
    );
  }

  //画像タグ
  private static function Get() {
    return '<img src="%s"%s%s>';
  }

  //ユーザアイコンタグ
  private static function GetIcon() {
    return '<img src="%s" alt="" style="border-color:%s;" align="middle"%s>';
  }

  //投票画面用アイコンタグ
  private static function GetVoteIcon() {
    return <<<EOF
<td><label for="%d">
<img src="%s" alt="" style="border-color:%s;"%s>
<span style="color:%s;">%s</span>%s<br>
%s</label></td>
EOF;
  }
}
