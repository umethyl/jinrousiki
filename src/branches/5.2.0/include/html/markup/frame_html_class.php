<?php
//-- HTML 生成クラス (frame 拡張) --//
final class FrameHTML {
  //ヘッダ出力
  public static function OutputHeader($title) {
    Text::Printf(self::GetHeader(), ServerConfig::ENCODE, $title);
  }

  //Frameset 出力 (縦分割)
  public static function OutputCol(array $list) {
    self::OutputSet('cols', $list);
  }

  //Frameset 出力 (横分割)
  public static function OutputRow(array $list) {
    self::OutputSet('rows', $list);
  }

  //Frame 出力
  public static function OutputSrc(array $list) {
    foreach ($list as $name => $src) {
      $tag  = 'frame';
      $tag .= HTML::GenerateAttribute('name', $name);
      $tag .= HTML::GenerateAttribute('src',  $src);
      Text::Output(HTML::GenerateTagHeader($tag));
    }
  }

  //フッタ出力
  public static function OutputFooter() {
    printf(self::GetFooter(), Message::NO_FRAME);
  }

  //Frameset 出力
  private static function OutputSet(string $name, array $list) {
    $tag  = 'frameset';
    $tag .= HTML::GenerateAttribute($name, ArrayFilter::Concat($list, ', '));
    $tag .= self::GenerateAttribute();
    Text::Output(HTML::GenerateTagHeader($tag));
  }

  //Frameset 共通 attribute 生成
  private static function GenerateAttribute() {
    $str  = '';
    $list = [
      'border'		=> 1,
      'frameborder'	=> 1,
      'framespacing'	=> 1,
      'bordercolor'	=> '#C0C0C0'
    ];
    foreach ($list as $name => $value) {
      $str .= HTML::GenerateAttribute($name, $value);
    }

    return $str;
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%s">
<title>%s</title>
</head>
EOF;
  }

  //フッタタグ
  private static function GetFooter() {
    return <<<EOF
<noframes>
<body>
%s
</body>
</noframes>
</frameset>
</html>
EOF;
  }
}
