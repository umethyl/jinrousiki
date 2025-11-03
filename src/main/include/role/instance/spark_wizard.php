<?php
/*
  ◆魔砲使い (spark_wizard)
  ○仕様
  ・魔法：直線暗殺
  ・天候：霧雨(最小1人) / 木枯らし(最小5人)
*/
RoleLoader::LoadFile('wizard');
class Role_spark_wizard extends Role_wizard {
  public $action = VoteAction::SPARK_WIZARD;
  public $submit = VoteAction::WIZARD;

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateSparkVoteNightTargetList($list);
  }

  //複合投票型夜投票無効判定 (魔砲使い用)
  protected function ValidateSparkVoteNightTargetList(array $list) {
    //-- 経路判定 --//
    sort($list);

    $id     = array_shift($list);
    $max    = DB::$USER->Count();
    $vector = null;
    $count  = 0;
    $root_list = [$id];
    while (count($list) > 0) {
      $chain = Position::GetFullChain($id, $max);
      $point = array_intersect($chain, $list);
      if (count($point) != 1) {
	throw new UnexpectedValueException(VoteRoleMessage::UNCHAINED_ROUTE);
      }

      $new_vector = ArrayFilter::PickKey($point);
      if ($new_vector != $vector) {
	if ($count++ > 0) {
	  throw new UnexpectedValueException(VoteRoleMessage::INVALID_ROUTE);
	}
	$vector = $new_vector;
      }

      $id = array_shift($point);
      $root_list[] = $id;
      ArrayFilter::Delete($list, $id);
    }
    $this->SetVoteNightTargetListRange($root_list);
  }

  //範囲暗殺
  public function SetLineAssassin(array $list) {
    //Text::p($list, "◆[{$this->role}] ({$this->GetActor()->uname})");

    //最小人数判定
    if (count($list) < $this->GetLineAssassinMinUserCount()) {
      return false;
    }

    //情報収集
    $camp_list   = [];
    $target_list = [];
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $camp_list[$user->GetWinCamp()] = true;

      //暗殺可能対象者判定
      if ($this->EnableLineAssassin($user)) {
	$target_list[] = $id;
      }
    }
    //Text::p($camp_list,   "◆[{$this->role}/camp] ({$this->GetActor()->uname})");
    //Text::p($target_list, "◆[{$this->role}/target] ({$this->GetActor()->uname})");

    //陣営数判定
    if (count($camp_list) != 1) {
      return false;
    }

    //範囲暗殺実行登録
    foreach ($target_list as $id) {
      $this->AddSuccess($id, RoleVoteSuccess::ASSASSIN);
    }
  }

  //範囲暗殺成立最小対象人数
  final protected function GetLineAssassinMinUserCount() {
    if (DB::$ROOM->IsEvent('full_wizard')) {
      return 1;
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      return 5;
    } else {
      return 3;
    }
  }

  //範囲暗殺有効判定 (対暗殺護衛 > 逃亡 > 反射)
  final protected function EnableLineAssassin(User $user) {
    //生存者のみ
    if ($user->IsDead(true)) {
      return false;
    }

    if (RoleUser::GuardAssassin($user)) {
      return false;
    }
    if (RoleUser::IsEscape($user)) {
      return false;
    }
    if (RoleUser::IsReflectAssassin($user)) {
      return false;
    }

    return true;
  }
}
