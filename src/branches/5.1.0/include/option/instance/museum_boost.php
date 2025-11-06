<?php
/*
  ◆博物館倍率モード (museum_boost)
*/
class Option_museum_boost extends OptionTextCheckbox {
  use OptionChaosBoost;

  public function GetCaption() {
    return '博物館倍率モード';
  }

  public function GetExplain() {
    return '旧バージョンの新規実装に倍率補正がかかります(<a href="info/chaos.php#museum_boost">詳細</a>)';
  }

  public function GetPlaceholder() {
    return '';
  }

  public function GetTextSize() {
    return 6;
  }
}
