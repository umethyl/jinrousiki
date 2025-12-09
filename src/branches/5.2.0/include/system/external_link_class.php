<?php
//-- 外部リンク生成の基底クラス --//
final class ExternalLinkBuilder {
  const TIME = 5; //タイムアウト時間 (秒)

  //サーバ通信状態チェック
  public static function IsConnect($url) {
    $stack = URL::Parse($url);
    $host  = $stack[2];
    $io    = @fsockopen($host, 80, $status, $str, self::TIME);
    if (! $io) {
      return false;
    }

    stream_set_timeout($io, self::TIME);
    $format = 'GET / HTTP/1.1%sHost: %s%sConnection: Close' . Text::CRLF . Text::CRLF;
    fwrite($io, sprintf($format, Text::CRLF, $host, Text::CRLF));
    $data   = fgets($io, 128);
    $stream = stream_get_meta_data($io);
    fclose($io);
    return ! $stream['timed_out'];
  }

  //出力
  public static function Output($title, $data) {
    HTML::OutputFieldsetHeader($title);
    DivHTML::Output(HTML::GenerateTag('dl', $data), [HTML::CSS => 'game-list']);
    HTML::OutputFieldsetFooter();
  }

  //タイムアウトメッセージ出力
  public static function OutputTimeOut($title, $url) {
    $stack  = URL::Parse($url);
    $format = '%s: Connection timed out (%d seconds)';
    self::Output($title, sprintf($format, $stack[2], self::TIME));
  }
}
