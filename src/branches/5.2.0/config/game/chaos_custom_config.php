<?php
//-- 配役設定(管理者設定用) --//
final class ChaosCustomConfig {
  //-- 博物館モード(追加型) --//
  /* ChaosConfig::$topping_list とルールは同じ */
  public static $museum_topping_list = [
    //TypeA: 管理者設定
    'a1' => [],
    //設定サンプル
    //'a1' => ['fix' => ['human' => 1]],
  ];

  //-- 博物館モード(倍率型) --//
  /* ChaosConfig::$boost_rate_list とルールは同じ */
  public static $museum_boost_list = [
    //TypeA: 管理者設定
    'a1'  => [],
    //設定サンプル
    //'a1'  => ['boost' => ['human']],
  ];
}
