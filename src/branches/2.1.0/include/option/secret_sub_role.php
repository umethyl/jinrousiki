<?php
/*
  ◆サブ役職を表示しない (secret_sub_role)
*/
class Option_secret_sub_role extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return 'サブ役職を表示しない'; }

  function GetExplain() { return 'サブ役職が分からなくなります：闇鍋モード専用オプション'; }
}
