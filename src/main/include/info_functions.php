<?php
//-- Info 情報生成クラス --//
final class Info {
  //リアルタイム制のアイコン出力
  public static function OutputRealTime() {
    $format = 'リアルタイム制　昼：%d分　夜： %d分';
    $str = sprintf($format, TimeConfig::DEFAULT_DAY,  TimeConfig::DEFAULT_NIGHT);
    echo ImageManager::Room()->Generate('real_time', $str);
  }
}
