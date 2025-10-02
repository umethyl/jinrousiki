<?php
/*
  ◆サブ役職をつけない (no_sub_role)
*/
OptionManager::Load('sub_role_limit_none');
class Option_no_sub_role extends Option_sub_role_limit_none {
  function GetCaption() { return 'サブ役職をつけない'; }
}
