<?php
/*
  ◆変装 (face_status)
  ○仕様
  ・役職表示：適合日のみ
  ・悪戯：アイコン差し替え
*/
class Role_face_status extends Role {
  protected function IgnoreAbility() {
    return false === $this->IsDoom();
  }

  public function BadStatus() {
    $stack = [];

    //悪戯対象者から該当日を抽出
    foreach (ArrayFilter::Get(DB::$USER->GetRole(), $this->role) as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDoomRole($this->role)) {
	//重なっている場合はランダムで1つ
	$target_id  = Lottery::Get(ArrayFilter::GetKeyList($user->GetPartner($this->role)));
	$target     = DB::$USER->ByID($target_id);
	$stack[$id] = ['icon' => $target->icon_filename, 'color' => $target->color];
      }
    }

    //アイコン差し替え
    //Text::p($stack, "◆$this->role");
    foreach ($stack as $id => $list) {
      $user = DB::$USER->ByID($id);
      $user->color         = $list['color'];
      $user->icon_filename = $list['icon'];
    }
  }
}
