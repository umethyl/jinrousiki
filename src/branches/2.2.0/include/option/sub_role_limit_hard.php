<?php
/*
  ◆サブ役職制限：HARDモード
*/
OptionManager::Load('sub_role_limit_none');
class Option_sub_role_limit_hard extends Option_sub_role_limit_none {
  function GetCaption() { return 'サブ役職制限：HARDモード'; }
}
