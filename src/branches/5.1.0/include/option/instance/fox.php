<?php
/*
  ◆妖狐追加 (fox)
  ○仕様
  ・配役：村人 → 妖狐
*/
OptionLoader::LoadFile('wolf');
class Option_fox extends Option_wolf {
  public function GetCaption() {
    return '妖狐追加';
  }

  public function GetExplain() {
    return '妖狐をもう一人追加します [村人1→妖狐1]';
  }
}
