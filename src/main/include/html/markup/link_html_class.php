<?php
//-- HTML 生成クラス (link 拡張) --//
final class LinkHTML {
  //タグ生成
  public static function Generate($url, $str) {
    $header = self::GenerateTagHeader($url);
    $footer = HTML::GenerateTagFooter('a');
    return $header . $str . $footer;
  }

  //ログへのリンク生成
  public static function GenerateLog($url, $watch = false, $header = '', $css = '', $footer = '') {
    $str = sprintf(self::GetLog(), $header,
      $url, $css, Message::LOG_NORMAL,
      $url, $css, Message::LOG_REVERSE,
      $url, $css, Message::LOG_DEAD,
      $url, $css, Message::LOG_DEAD_REVERSE,
      $url, $css, Message::LOG_HEAVEN,
      $url, $css, Message::LOG_HEAVEN_REVERSE
    );

    if (true === $watch) {
      $str .= sprintf(Text::LF . self::GetWatchLog(),
	$url, $css, Message::LOG_WATCH,
	$url, $css, Message::LOG_WATCH_REVERSE
      );
    }
    return $str . $footer;
  }

  //出力
  public static function Output($url, $str, $line = false) {
    Text::Output(self::Generate($url, $str), $line);
  }

  //タグヘッダ生成
  private static function GenerateTagHeader($url) {
    return HTML::GenerateTagHeader('a' . HTML::GenerateAttribute('href', $url));
  }

  //ログのタグ
  private static function GetLog() {
    return <<<EOF
%s <a target="_top" href="%s"%s>%s</a>
<a target="_top" href="%s&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&heaven_talk=on"%s>%s</a>
<a target="_top" href="%s&heaven_talk=on&reverse_log=on"%s>%s</a>
<a target="_top" href="%s&heaven_only=on"%s >%s</a>
<a target="_top" href="%s&heaven_only=on&reverse_log=on"%s>%s</a>
EOF;
  }

  //ログのタグ (観戦モード用)
  private static function GetWatchLog() {
    return <<<EOF
<a target="_top" href="%s&watch=on"%s>%s</a>
<a target="_top" href="%s&watch=on&reverse_log=on"%s>%s</a>
EOF;
  }
}
