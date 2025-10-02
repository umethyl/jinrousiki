<?php
/*
  ◆サブ役職制限：EASYモード
*/
OptionManager::Load('sub_role_limit_none');
class Option_sub_role_limit_easy extends Option_sub_role_limit_none {
  function GetCaption() { return 'サブ役職制限：EASYモード'; }
}
