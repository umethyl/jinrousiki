<?php
//-- HTML 生成クラス (GameFrame 拡張) --//
class GameFrameHTML {
  //出力
  public static function Output() {
    HTML::OutputFrameHeader(ServerConfig::TITLE . GameMessage::TITLE);
    RQ::Get()->dead_mode ? self::OutputHeavenFrame() : self::OutputFrame();
    HTML::OutputFrameFooter();
  }

  //フレーム出力
  private static function OutputFrame() {
    $url = RQ::Get()->url;
    Text::Printf(self::GetFrame(), $url, $url);
  }

  //フレーム出力 (霊界用)
  private static function OutputHeavenFrame() {
    $url = RQ::Get()->url . RQ::Get()->ToURL(RequestDataRoom::DEAD);
    Text::Printf(self::GetHeavenFrame(), $url, $url, RQ::Get()->url);
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
