<?php
/*
  ◆博物館追加モード (museum_topping)
*/
class Option_museum_topping extends OptionTextCheckbox {
  use OptionChaosTopping;

  public function GetCaption() {
    return '博物館追加モード';
  }

  public function GetExplain() {
    return '旧バージョンの新規実装から固定枠を追加します(<a href="info/chaos.php#museum_topping">詳細</a>)';
  }

  public function GetPlaceholder() {
    return '';
  }

  public function GetTextSize() {
    return 4;
  }
}
