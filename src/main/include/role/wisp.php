<?php
/*
  ◆鬼火 (wisp)
  ○仕様
  ・占い結果：鬼
*/
class Role_wisp extends Role {
  //占い判定妨害結果取得
  public function GetJammerMageResult(User $user, $reverse) {
    return $this->IgnoreJammerMageResult($user) ? null : $this->GetWispRole($reverse);
  }

  //占い判定妨害スキップ判定
  protected function IgnoreJammerMageResult(User $user) {
    return false;
  }

  //占い結果取得
  protected function GetWispRole($reverse) {
    return 'ogre';
  }
}
