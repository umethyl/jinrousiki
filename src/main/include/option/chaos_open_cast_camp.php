<?php
/*
  ◆陣営通知 (chaos_open_cast_camp)
*/
OptionManager::Load('chaos_open_cast_none');
class Option_chaos_open_cast_camp extends Option_chaos_open_cast_none {
  function GetName() { return '陣営通知'; }

  function GetCaption() { return '配役を通知する:陣営通知'; }

  function GetExplain() { return '陣営通知 (陣営ごとの合計を通知)'; }
}
