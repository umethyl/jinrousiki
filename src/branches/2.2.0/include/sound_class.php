<?php
//-- 音源処理クラス --//
class Sound {
  const PATH_FORMAT   = '%s/%s/%s.%s';
  const CLASS_ID      = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';
  const CODEBASE_URL  = 'http://download.macromedia.com/pub/shockwave/cabs/flash/';
  const CODEBASE_FILE = 'swflash.cab#version=4,0,0,0';
  const TYPE          = 'application/x-shockwave-flash';
  const EMBED_URL     = 'http://www.macromedia.com/shockwave/download/';
  const EMBED_FILE    = 'index.cgi?P1_Prod_Version=ShockwaveFlash';

  //HTML 生成 (JavaScript 用)
  static function Generate($type) {
    $format = "<object classid='%s' codebase='%s%s' width='0' height='0'>" .
      "<param name='movie' value='%s'><param name='quality' value='high'>" .
      "<embed src='%s' type='%s' quality='high' width='0' height='0' loop='false'" .
      " pluginspage='%s%s'></object>";
    return self::Convert($format, $type);
  }

  //出力
  static function Output($type) {
    $format = <<<EOF
<object classid="%s" codebase="%s%s" width="0" height="0">
<param name="movie" value="%s">
<param name="quality" value="high">
<embed src="%s" type="%s" quality="high" width="0" height="0" loop="false" pluginspage="%s%s">
</object>%s
EOF;
    echo self::Convert($format, $type);
  }

  //フォーマット変換
  private static function Convert($format, $type) {
    $path = sprintf(self::PATH_FORMAT, JINRO_ROOT, SoundConfig::PATH, SoundConfig::$$type,
		    SoundConfig::EXTENSION);

    return sprintf($format, self::CLASS_ID, self::CODEBASE_URL, self::CODEBASE_FILE,
		   $path, $path, self::TYPE, self::EMBED_URL, self::EMBED_FILE, "\n");
  }
}
