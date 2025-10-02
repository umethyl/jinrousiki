<?php
/*
  ◆サブ役職を表示しない (secret_sub_role)
*/
class Option_secret_sub_role extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return 'サブ役職を表示しない';
  }

  public function GetExplain() {
    return 'サブ役職が分からなくなります：闇鍋モード専用オプション';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }
}
