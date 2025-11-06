<?php
//-- HTML 生成クラス (GameFrame 拡張) --//
final class GameFrameHTML {
  //出力
  public static function Output() {
    FrameHTML::OutputHeader(ServerConfig::TITLE . GameMessage::TITLE);
    RQ::Fetch()->dead_mode ? self::OutputHeavenFrame() : self::OutputFrame();
    FrameHTML::OutputFooter();
  }

  //フレーム出力
  private static function OutputFrame() {
    $url = RQ::Fetch()->url;
    Text::Printf(self::GetFrame(), $url, $url);
  }

  //フレーム出力 (霊界用)
  private static function OutputHeavenFrame() {
    $url = RQ::Fetch()->url . RQ::Fetch()->ToURL(RequestDataRoom::DEAD);
    Text::Printf(self::GetHeavenFrame(), $url, $url, RQ::Fetch()->url);
  }

  //フレームタグ
  private static function GetFrame() {
    return <<<EOF
<frameset rows="100, *" border="1" frameborder="1" framespacing="1" bordercolor="#C0C0C0">
<frame name="up" src="game_up.php%s">
<frame name="bottom" src="game_play.php%s">
EOF;
  }

  //フレームタグ (霊界用)
  private static function GetHeavenFrame() {
    return <<<EOF
<frameset rows="100, *, 20%%" border="1" frameborder="1" framespacing="1" bordercolor="#C0C0C0">
<frame name="up" src="game_up.php%s&heaven_mode=on">
<frame name="middle" src="game_play.php%s">
<frame name="bottom" src="game_play.php%s&heaven_mode=on">
EOF;
  }
}
