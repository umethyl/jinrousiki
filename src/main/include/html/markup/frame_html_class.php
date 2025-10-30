<?php
//-- HTML 生成クラス (frame 拡張) --//
final class FrameHTML {
  //ヘッダ出力
  public static function OutputHeader($title) {
    Text::Printf(self::GetHeader(), ServerConfig::ENCODE, $title);
  }

  //フッタ出力
  public static function OutputFooter() {
    printf(self::GetFooter(), Message::NO_FRAME);
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
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
