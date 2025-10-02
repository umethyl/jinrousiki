<?php
/*
  ◆魂移使 (exchange_angel)
  ○仕様
  ・共感者判定：特殊 (集計後)
*/
RoleManager::LoadFile('angel');
class Role_exchange_angel extends Role_angel {
  protected function IsSympathy(User $a, User $b) { return false; }

  //交換憑依処理
  final function Exchange() {
    //変数を初期化
    $angel_list    = array();
    $lovers_list   = array();
    $fix_list      = array();
    $exchange_list = array();
    foreach (DB::$USER->rows as $user) { //魂移使が打った恋人の情報を収集
      if ($user->IsDummyBoy() || ! $user->IsLovers()) continue;
      foreach ($user->GetPartner('lovers') as $cupid_id) {
	if (DB::$USER->ById($cupid_id)->IsRole('exchange_angel')) {
	  $angel_list[$cupid_id][]  = $user->id;
	  $lovers_list[$user->id][] = $cupid_id;
	  if ($user->IsPossessedGroup()) $fix_list[$cupid_id] = true; //憑依能力者なら対象外
	}
      }
    }
    //Text::p($angel_list, 'angel: 1st');
    //Text::p($lovers_list, 'lovers: 1st');

    foreach ($angel_list as $id => $lovers_stack) { //抽選処理
      if (array_key_exists($id, $fix_list)) continue;
      $duplicate_stack = array();
      //Text::p($fix_list, 'fix_angel:'. $id);
      foreach ($lovers_stack as $lovers_id) {
	foreach ($lovers_list[$lovers_id] as $cupid_id) {
	  if (! array_key_exists($cupid_id, $fix_list)) $duplicate_stack[$cupid_id] = true;
	}
      }
      //Text::p($duplicate_stack, 'duplicate:' . $id);
      $duplicate_list = array_keys($duplicate_stack);
      if (count($duplicate_list) > 1) {
	$exchange_list[] = Lottery::Get($duplicate_list);
	foreach ($duplicate_list as $duplicate_id) $fix_list[$duplicate_id] = true;
      }
      else {
	$exchange_list[] = $id;
      }
      $fix_list[$id] = true;
    }
    //Text::p($exchange_list, 'exchange');

    foreach ($exchange_list as $id) {
      $target_list = $angel_list[$id];
      $a = DB::$USER->ByID($target_list[0]);
      $b = DB::$USER->ByID($target_list[1]);
      $a->AddRole(sprintf('possessed_exchange[%s]', $target_list[1]));
      $b->AddRole(sprintf('possessed_exchange[%s]', $target_list[0]));
      $this->SetSympathy($a, $b);
    }
  }
}
