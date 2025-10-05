<?php
//-- HTML 生成クラス (音源処理拡張) --//
final class SoundHTML {
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
    return sprintf($format, Sound::GetPath($type));
  }

  //タグ
  private static function Get() {
    return '<audio src="%s" autoplay></audio>';
  }

  //タグ (JavaScript 用)
  private static function GetGenerate() {
    return "<audio src='%s' autoplay></audio>";
  }
}
