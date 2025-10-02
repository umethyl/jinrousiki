<?php
/*
  ◆虚弱体質村 (sudden_death)
  ○仕様
  ・配役：全員に小心者系のどれか
*/
class Option_sudden_death extends CheckRoomOptionItem {
  public $disable_list = array('febris', 'frostbite', 'death_warrant', 'panelist');

  public function GetCaption() {
    return '虚弱体質村';
  }

  public function GetExplain() {
    return '全員に投票でショック死するサブ役職のどれかがつきます';
  }

  protected function GetCastAllRole($id) {
    if (Cast::Stack()->IsEmpty($this->name)) { //未セットなら初期化
      $stack = $this->GetSuddenDeathList();
      Cast::Stack()->Set($this->name, $stack);
    } else {
      $stack = Cast::Stack()->Get($this->name);
    }

    $role = Lottery::Get($stack);
    if ($role == 'impatience') { //短気は一人だけ
      Cast::Stack()->DeleteDiff($this->name, array($role));
    }
    return $role;
  }

  protected function GetResultCastList() {
    return $this->GetSuddenDeathList();
  }

  //配役対象小心者系リスト取得
  private function GetSuddenDeathList() {
    return array_diff(RoleData::$sub_role_group_list['sudden-death'], $this->disable_list);
  }
}
