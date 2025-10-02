<?php
//-- HTML 生成クラス (音源処理拡張) --//
class SoundHTML {
  const CLASS_ID      = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';
  const CODEBASE_URL  = 'http://download.macromedia.com/pub/shockwave/cabs/flash/';
  const CODEBASE_FILE = 'swflash.cab#version=4,0,0,0';
  const TYPE          = 'application/x-shockwave-flash';
  const EMBED_URL     = 'http://www.macromedia.com/shockwave/download/';
  const EMBED_FILE    = 'index.cgi?P1_Prod_Version=ShockwaveFlash';

  //生成 (JavaScript 用)
  public static function Generate($type) {
    return self::Format(self::GetGenerate(), $type);
  }

  //出力
  public static function Output($type) {
    Text::Output(self::Format(self::Get(), $type));
  }

  //整形
  private static function Format($format, $type) {
    $path = Sound::GetPath($type);
    return sprintf($format,
      self::CLASS_ID, self::CODEBASE_URL, self::CODEBASE_FILE,
      $path, $path, self::TYPE, self::EMBED_URL, self::EMBED_FILE
    );
  }

  //タグ
  private static function Get() {
    return <<<EOF
<object classid="%s" codebase="%s%s" width="0" height="0">
<param name="movie" value="%s">
<param name="quality" value="high">
<embed src="%s" type="%s" quality="high" width="0" height="0" loop="false" pluginspage="%s%s">
</object>
EOF;
  }

  //タグ (JavaScript 用)
  private static function GetGenerate() {
    return "<object classid='%s' codebase='%s%s' width='0' height='0'>" .
      "<param name='movie' value='%s'><param name='quality' value='high'>" .
      "<embed src='%s' type='%s' quality='high' width='0' height='0' loop='false'" .
      " pluginspage='%s%s'></object>";
  }
}
