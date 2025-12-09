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
    $url  = RQ::Get('url');
    $list = [
      'up'	=> 'game_up.php'   . $url,
      'bottom'	=> 'game_play.php' . $url
    ];

    FrameHTML::OutputRow([100, '*']);
    FrameHTML::OutputSrc($list);
  }

  //フレーム出力 (霊界用)
  private static function OutputHeavenFrame() {
    $url  = RQ::Get('url') . RQ::Fetch()->ToURL(RequestDataRoom::DEAD);
    $list = [
      'up'	=> 'game_up.php'   . $url . URL::AddSwitch(RequestDataRoom::HEAVEN),
      'middle'	=> 'game_play.php' . $url,
      'bottom'	=> 'game_play.php' . RQ::Get('url') . URL::AddSwitch(RequestDataRoom::HEAVEN)
    ];

    FrameHTML::OutputRow([100, '*', '20%']);
    FrameHTML::OutputSrc($list);
  }
}
