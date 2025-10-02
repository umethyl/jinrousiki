<?php
/*
  ◆猩々 (clairvoyance_scanner)
  ○仕様
  ・追加役職：なし
  ・投票結果：透視
  ・投票：2日目以降
*/
RoleManager::LoadFile('mind_scanner');
class Role_clairvoyance_scanner extends Role_mind_scanner {
  public $result = 'CLAIRVOYANCE_RESULT';
  public $action_date_type = 'after';
  public $mind_role = null;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  /*
    複数の投票イベントを持つタイプが出現した場合は複数のメッセージを発行する必要がある
    対象が NULL でも有効になるタイプ (キャンセル投票はスキップ) は想定していない
  */
  public function Report(User $user) {
    foreach ($this->GetStack('vote_data') as $action => $vote_stack) {
      if (strpos($action, '_NOT_DO') !== false || ! array_key_exists($user->id, $vote_stack)) {
	continue;
      }
      $actor_id     = $this->GetID();
      $target_name  = $user->GetName();
      $target_stack = $vote_stack[$user->id];

      if ($user->IsRole('barrier_wizard')) { //結界師
	$result_stack = array();
	foreach (explode(' ', $target_stack) as $id) { //憑依を追跡する
	  $target = DB::$USER->ByVirtual($id);
	  $result_stack[$target->id] = $target->handle_name;
	}
	ksort($result_stack);
	foreach ($result_stack as $result) {
	  DB::$ROOM->ResultAbility($this->result, $result, $target_name, $actor_id);
	}
      }
      //審神者・山立・響狼・文武王
      elseif ($user->IsRole('step_mage', 'step_guard', 'step_wolf', 'step_vampire')) {
	$id_stack = explode(' ', $target_stack);
	$target   = DB::$USER->ByVirtual(array_pop($id_stack)); //最終到達点は憑依を追跡する
	$result_stack = array($target->id => $target->handle_name);
	foreach ($id_stack as $id) {
	  $result_stack[$id] = DB::$USER->ByID($id)->handle_name;
	}
	ksort($result_stack);
	foreach ($result_stack as $result) {
	  DB::$ROOM->ResultAbility($this->result, $result, $target_name, $actor_id);
	}
      }
      //家鳴・響狐
      elseif ($user->IsRole('step_assassin', 'step_scanner', 'step_mad', 'step_fox')) {
	$result_stack = array();
	foreach (explode(' ', $target_stack) as $id) { //憑依を追跡しない
	  $result_stack[$id] = DB::$USER->ByID($id)->handle_name;
	}
	ksort($result_stack);
	foreach ($result_stack as $result) {
	  DB::$ROOM->ResultAbility($this->result, $result, $target_name, $actor_id);
	}
      }
      else {
	$result = DB::$USER->ByVirtual($target_stack)->handle_name;
	DB::$ROOM->ResultAbility($this->result, $result, $target_name, $actor_id);
      }
    }
  }
}
