<?php
/*
  ◆琵琶牧々 (lute_mania)
  ○仕様
  ・足音：コピー先縦軸
*/
RoleManager::LoadFile('unknown_mania');
class Role_lute_mania extends Role_unknown_mania {
  //足音処理
  public function Step() {
    $list  = RoleManager::GetStack('vote_data');
    $id    = $list['MANIA_DO'][$this->GetID()];
    $stack = array();
    foreach ($this->LotteryChainStep($this->GetChainStep($id), $id) as $target_id) {
      if (DB::$USER->IsVirtualLive($target_id)) $stack[] = $target_id;
    }
    return DB::$ROOM->ResultDead(implode(' ', $stack), 'STEP');
  }

  //足音範囲取得
  protected function GetChainStep($id) {
    $stack = array();
    $count = DB::$USER->GetUserCount();
    for ($i = $id % 5; $i <= $count; $i += 5) {
      if ($i > 0) $stack[] = $i;
    }
    return $stack;
  }

  //足音範囲抽選処理
  protected function LotteryChainStep(array $list, $id) {
    $length = Lottery::GetRange(1, count($list));
    //Text::p($list,   '◆ChainStep [base]');
    //Text::p($length, '◆ChainStep [length]');

    $max   = count($list);
    $stack = array();
    for ($i = 0; $i < $max; $i++) {
      if ($i + $length > $max) break;
      $slice = array_slice($list, $i, $length);
      if (in_array($id, $slice)) $stack[] = $slice;
    }
    //Text::p($stack, '◆ChainStep [slice]');

    return Lottery::Get($stack);
  }
}
