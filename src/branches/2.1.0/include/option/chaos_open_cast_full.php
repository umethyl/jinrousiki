<?php
/*
  ◆完全通知 (chaos_open_cast_full)
*/
OptionManager::Load('chaos_open_cast_none');
class Option_chaos_open_cast_full extends Option_chaos_open_cast_none {
  function GetName() { return '完全通知'; }

  function GetCaption() { return '配役を通知する:完全通知'; }

  function GetExplain() { return '完全通知 (通常村相当)'; }
}
