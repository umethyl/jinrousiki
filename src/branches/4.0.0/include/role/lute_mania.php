<?php
/*
  ◆琵琶牧々 (lute_mania)
  ○仕様
  ・足音：コピー先縦軸
*/
RoleLoader::LoadFile('unknown_mania');
class Role_lute_mania extends Role_unknown_mania {
  public $mix_in = ['step_mage'];

  //足音処理
  public function Step() {
    if ($this->IgnoreStep()) return false;

    $list  = RoleManager::GetVoteData();
    $id    = $list[$this->action][$this->GetID()];
    $stack = [];
    foreach ($this->LotteryChainStep($this->GetChainStep($id), $id) as $target_id) {
      if (DB::$USER->IsVirtualLive($target_id)) {
	$stack[] = $target_id;
      }
    }
    return DB::$ROOM->ResultDead(ArrayFilter::Concat($stack), DeadReason::STEP);
  }

  //足音範囲抽選処理
  final protected function LotteryChainStep(array $list, $id) {
    $length = Lottery::GetRange(1, count($list));
    //Text::p($list,   '◆ChainStep [base]');
    //Text::p($length, '◆ChainStep [length]');

    $max   = count($list);
    $stack = [];
    for ($i = 0; $i < $max; $i++) {
      if ($i + $length > $max) break;
      $slice = array_slice($list, $i, $length);
      if (in_array($id, $slice)) {
	$stack[] = $slice;
      }
    }
    //Text::p($stack, '◆ChainStep [slice]');

    return Lottery::Get($stack);
  }

  //足音範囲取得
  protected function GetChainStep($id) {
    return Position::GetVertical($id);
  }
}
