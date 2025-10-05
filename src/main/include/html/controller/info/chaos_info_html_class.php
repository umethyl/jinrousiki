<?php
//-- HTML 生成クラス (ChaosInfo 拡張) --//
final class ChaosInfoHTML {
  //オプション出力
  public static function Output($option, $name, $version) {
    Text::Printf(self::Get(),
      $option, $name, GameOptionConfig::${$option.'_list'}[$name], $version, Message::RANGE
    );
  }

  //オプションリスト出力
  public static function OutputList($option, array $list) {
    foreach ($list as $name) {
      HTML::OutputLink('#' . $option . '_' . $name, GameOptionConfig::${$option.'_list'}[$name]);
    }
  }

  //オプションタグ
  private static function Get() {
    return '<h3 id="%s_%s">%s [%s%s]</h3>';
  }
}
