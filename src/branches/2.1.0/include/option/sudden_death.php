<?php
/*
  ◆虚弱体質村 (sudden_death)
  ○仕様
*/
class Option_sudden_death extends CheckRoomOptionItem {
  public $disable_list = array('febris', 'frostbite', 'death_warrant', 'panelist');

  function GetCaption() { return '虚弱体質村'; }

  function GetExplain() { return '全員に投票でショック死するサブ役職のどれかがつきます'; }

  function Cast(array &$list, &$rand) {
    $stack = array_diff(RoleData::$sub_role_group_list['sudden-death'], $this->disable_list);
    $role_list = $stack;
    foreach (array_keys($list) as $id) { //全員に小心者系を何かつける
      $role = Lottery::Get($stack);
      $list[$id] .= ' ' . $role;
      if ($role == 'impatience') $stack = array_diff($stack, array('impatience')); //短気は一人だけ
    }
    return $role_list;
  }
}
