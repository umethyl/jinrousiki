<?php
/*
  ◆悪寒 (chill_febris)
  ○仕様
  ・役職表示：熱病
  ・表示：当日限定
  ・ショック死：発動当日 (15%)
*/
RoleLoader::LoadFile('febris');
class Role_chill_febris extends Role_febris {
  public $display_role = 'febris';

  protected function IgnoreResult() {
    return $this->GetActor()->IsDoomRole('febris');
  }

  protected function IgnoreSuddenDeathFebris() {
    return ! Lottery::Percent(15);
  }
}
