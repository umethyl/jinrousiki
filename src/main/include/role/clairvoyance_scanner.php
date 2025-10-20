<?php
/*
  ◆猩々 (clairvoyance_scanner)
  ○仕様
  ・追加役職：なし
  ・能力結果：透視
  ・投票：2 日目以降
*/
RoleLoader::LoadFile('mind_scanner');
class Role_clairvoyance_scanner extends Role_mind_scanner {
  public $result = RoleAbility::CLAIRVOYANCE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  protected function GetMindRole() {
    return null;
  }

  public function MindScan(User $user) {
    foreach (RoleLoader::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->TrapKill($this->GetActor(), $user->id)) {
	return false;
      }
    }

    /*
      複数の投票イベントを持つタイプが出現した場合は複数のメッセージを発行する必要がある
      対象が NULL でも有効になるタイプ (キャンセル投票はスキップ) は想定していない
    */
    foreach (RoleManager::GetVoteData() as $action => $vote_stack) {
      if (Text::Search($action, '_NOT_DO') || false === isset($vote_stack[$user->id])) {
	continue;
      }
      $actor_id     = $this->GetID();
      $target_name  = $user->GetName();
      $target_stack = $vote_stack[$user->id];

      //結界師 > 足音能力者/本人起点型 > 足音能力者/直線型 > その他
      if ($user->IsRole('barrier_wizard')) {
	$result_stack = [];
	foreach (Text::Parse($target_stack) as $id) { //憑依を追跡する
	  $target = DB::$USER->ByVirtual($id);
	  $result_stack[$target->id] = $target->handle_name;
	}
      } elseif ($user->IsRole('step_mage', 'step_guard', 'step_wolf', 'step_vampire')) {
	$id_stack = Text::Parse($target_stack);
	$target   = DB::$USER->ByVirtual(array_pop($id_stack)); //最終到達点は憑依を追跡する
	$result_stack = [$target->id => $target->handle_name];
	foreach ($id_stack as $id) {
	  $result_stack[$id] = DB::$USER->ByID($id)->handle_name;
	}
      } elseif ($user->IsRole('step_assassin', 'step_scanner', 'step_mad', 'step_fox')) {
	$result_stack = [];
	foreach (Text::Parse($target_stack) as $id) { //憑依を追跡しない
	  $result_stack[$id] = DB::$USER->ByID($id)->handle_name;
	}
      } else {
	$result_stack = [DB::$USER->ByVirtual($target_stack)->handle_name];
      }

      ksort($result_stack);
      foreach ($result_stack as $result) {
	DB::$ROOM->StoreAbility($this->result, $result, $target_name, $actor_id);
      }
    }
  }
}
