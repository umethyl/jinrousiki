<?php
//-- バージョン判定クラス --//
final class JinrouVersion {
  //最新判定
  public static function Newer($current, $target) {
    $current_type = self::Parse($current);
    $target_type  = self::Parse($target);

    if ($current_type['type'] != $target_type['type']) {
      return $current_type['type'] > $target_type['type'];
    } else {
      return $current_type['revision'] >= $target_type['revision'];
    }
  }

  //過去判定
  public static function Older($revision) {
    $current_type = self::Parse(ServerConfig::REVISION);
    $target_type  = self::Parse($revision);

    if ($current_type['type'] != $target_type['type']) {
      return $current_type['type'] < $target_type['type'];
    } else {
      return Number::Within($current_type['revision'], 0, $target_type['revision']);
    }
  }

  //Revision パース
  private static function Parse($revision) {
    if (is_int($revision)) {
      return ['type' => 1, 'revision' => $revision];
    } else {
      return ['type' => 2, 'revision' => Text::CutPop($revision, '#')];
    }
  }
}
