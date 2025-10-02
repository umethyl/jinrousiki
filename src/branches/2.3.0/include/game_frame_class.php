<?php
//-- GameFrame 出力クラス --//
//-- ◆ 文字化け抑制 --//
class GameFrame {
  const BORDER = ' border="1" frameborder="1" framespacing="1" bordercolor="#C0C0C0"';

  //出力
  static function Output() {
    HTML::OutputFrameHeader(ServerConfig::TITLE . GameMessage::TITLE);
    RQ::Get()->dead_mode ? self::OutputHeaven() : self::OutputNormal();
    HTML::OutputFrameFooter();
  }

  //霊界画面出力
  private static function OutputHeaven() {
    $format = <<<EOF
<frameset rows="100, *, 20%%"%s>
<frame name="up" src="game_up.php%s&heaven_mode=on">
<frame name="middle" src="game_play.php%s">
<frame name="bottom" src="game_play.php%s">
EOF;
    $url = RQ::Get()->url . '&dead_mode=on';
    printf($format . Text::LF, self::BORDER, $url, $url, RQ::Get()->url . '&heaven_mode=on');
  }

  //通常画面出力
  private static function OutputNormal() {
    $format = <<<EOF
<frameset rows="100, *"%s>
<frame name="up" src="game_up.php%s">
<frame name="bottom" src="game_play.php%s">
EOF;
    $url = RQ::Get()->url;
    printf($format . Text::LF, self::BORDER, $url, $url);
  }
}
