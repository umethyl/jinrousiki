<?php
/*
  ◆神話マニア登場 (mania)
  ○仕様
  ・配役：村人 → 神話マニア
*/
class Option_mania extends CheckRoomOptionItem {
  function GetCaption() { return '神話マニア登場'; }

  function GetExplain() { return '初日夜に他の村人の役職をコピーします [村人1→神話マニア1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} && ! DB::$ROOM->IsOption('full_' . $this->name) &&
	$list['human'] > 0) {
      $list['human']--;
      $list[$this->name]++;
    }
  }
}
