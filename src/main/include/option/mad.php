<?php
/*
  ◆狂人追加 (mad)
  ○仕様
  ・配役：村人 → 狂人
*/
OptionLoader::LoadFile('wolf');
class Option_mad extends Option_wolf {
  public function GetCaption() {
    return '狂人追加';
  }

  public function GetExplain() {
    return '狂人をもう一人追加します [村人1→狂人1]';
  }
}
