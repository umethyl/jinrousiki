<?php
/*
  ◆大魔縁 (involve_tengu)
  ○仕様
  ・神通力：神隠し (50%)
  ・身代わり君人狼襲撃：神隠し
  ・人狼襲撃：神隠し
*/
RoleLoader::LoadFile('meteor_tengu');
class Role_involve_tengu extends Role_meteor_tengu {
  public function WolfEatDummyBoyCounter() {
    $this->InvolveTenguKill(true);
  }

  public function WolfEatCounter(User $user) {
    $this->InvolveTenguKill(DB::$ROOM->IsDate(1));
  }

  protected function GetTenguMageRateBase() {
    return 50;
  }

  //巻き添え神隠し処理
  public function InvolveTenguKill($start = false) {
    //陣営情報収集
    $camp_list = [];
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsDead()) {
	continue;
      }
      $camp_list[$user->GetMainCamp($start)][] = $user->id; //キャッシュを参照しない
    }
    //Text::p($camp_list, "◆Camp/Base [{$this->role}]");

    //陣営人数を収集
    $count_list = [];
    foreach ($camp_list as $camp => $list) {
      $count_list[$camp] = count($list);
    }
    //Text::p($count_list, "◆Camp/Count [{$this->role}]");

    //最大人数陣営を決定
    $max   = max($count_list);
    $stack = [];
    foreach ($camp_list as $camp => $list) {
      if (count($list) == $max) {
	$stack[] = $camp;
      }
    }
    $camp = Lottery::Get($stack);
    //Text::p($camp, "◆Camp [{$this->role}]");

    //対象者を収集
    $stack = [];
    foreach ($camp_list[$camp] as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsRole('lovers') || RoleUser::Avoid($user)) { //対象外判定
	continue;
      }
      $stack[] = $id;
    }
    //Text::p($stack, "◆Target [{$this->role}]");

    //神隠し処理
    if (count($stack) > 0) {
      $this->TenguKill(DB::$USER->ByID(Lottery::Get($stack)));
    }
  }
}
