<?php
/*
  ◆役職通知 (chaos_open_cast_role)
*/
OptionManager::Load('chaos_open_cast_none');
class Option_chaos_open_cast_role extends Option_chaos_open_cast_none {
  function GetName() { return '役職通知'; }

  function GetCaption() { return '配役を通知する:役職通知'; }

  function GetExplain() { return '役職通知 (役職の種類別に合計を通知)'; }
}
