<?php
/*
  ◆従者 (servant)
  ○仕様
  ・投票コマンド：初日：主選択 / 二日目以降：主裏切り
  ・仲間表示：自分が選んだ主
  ・主支援役職：従者支援
  ・主裏切り：従者支援変化
  ・主裏切り追加処理：なし
  ・従者支援(投票数)：変化なし
  ・従者支援(得票数)：支援：-1 / 裏切り：+1
*/
class Role_servant extends RoleAbility_servant {
  protected function GetServantTargetRole() {
    return $this->GetActor()->GetID('serve_support');
  }

  protected function GetVotePollCount() {
    //生存時のみ有効
    $user = $this->GetActor();
    if ($user->IsDead()) {
      return 0;
    }

    //裏切り判定
    return $user->IsActive() ? -1 : +1;
  }
}

//-- 従者の基礎クラス --//
class RoleAbility_servant extends Role {
  protected function GetAction(string $name) {
    switch ($name) {
    case 'action':
      if (DateBorder::One()) {
	return VoteAction::SERVE;
      } else {
	return VoteAction::SERVE_END;
      }

    case 'not_action':
      if (DateBorder::One()) {
	return null;
      } else {
	return VoteAction::NOT_SERVE_END;
      }
    }
  }

  protected function SetAction() {
    foreach (['action', 'not_action'] as $name) {
      $this->$name = $this->GetAction($name);
    }
  }

  protected function IsAddVote() {
    if (DateBorder::One()) {
      return true;
    } else {
      //主不在の場合は無効
      $user = $this->GetActor();
      if (null === $user->GetMainRoleTarget()) {
	return false;
      }

      //能力喪失判定
      return $user->IsActive();
    }
  }

  protected function IgnorePartner() {
    return DateBorder::PreTwo();
  }

  protected function GetPartner() {
    $id = $this->GetActor()->GetMainRoleTarget();
    if (null === $id) {
      return [];
    }

    $user  = DB::$USER->ByID($id);
    $stack = [$user->handle_name]; //憑依追跡なし
    return ['servant_target' => $stack];
  }

  public function OutputAction() {
    if (DateBorder::One()) {
      $str = RoleAbilityMessage::SERVANT;
      RoleHTML::OutputVoteNight(VoteCSS::SERVANT, $str, $this->action);
    } else {
      $str = RoleAbilityMessage::SERVANT_END;
      RoleHTML::OutputVoteNight(VoteCSS::SERVANT, $str, $this->action, $this->not_action);
    }
  }

  protected function MatchVote() {
    //投票イベント実行時のみ判定する
    $post = RQ::Get(RequestDataVote::SITUATION);
    if (null === $post) {
      return true;
    }

    if (DateBorder::One()) {
      $list = [VoteAction::SERVE];
    } else {
      $list = [VoteAction::SERVE_END, VoteAction::NOT_SERVE_END];
    }
    return ArrayFilter::IsInclude($list, $post);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function GetVoteNightTargetUserFilter(array $list) {
    if (DateBorder::One()) {
      return $list;
    } else {
      $id = $this->GetActor()->GetMainRoleTarget();
      return [$id => $list[$id]];
    }
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  protected function IsVoteNightCheckboxFilter(User $user) {
    if (DateBorder::One()) {
      return true;
    } else {
      $id = $this->GetActor()->GetMainRoleTarget();
      return $user->GetID() == $id;
    }
  }

  protected function CheckedVoteNightCheckbox(User $user) {
    if (DateBorder::One()) {
      return false;
    } else {
      return true;
    }
  }

  protected function ValidateVoteNightTargetFilter(User $user) {
    if (true !== $this->IsVoteNightCheckboxFilter($user)) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_INVALID);
    }
  }

  //-- 従者固有能力 --//
  //主選択処理
  final public function ServantSelect(User $user) {
    //選択した主を登録
    $actor = $this->GetActor();
    $actor->AddMainRole($user->id);

    //主に支援役職を付与
    $role = $this->GetServantTargetRole();
    if (null !== $role) {
      $user->AddRole($role);
    }
  }

  //主支援役職取得
  protected function GetServantTargetRole() {
    return null;
  }

  //主裏切り処理
  final public function ServantEnd(User $user) {
    //支援終了 (能力喪失)
    $this->GetActor()->LostAbility();

    //追加処理
    $this->ServantEndAction($user);
  }

  //主裏切り追加処理
  protected function ServantEndAction(User $user) {}

  //-- 従者支援用 --//
  protected function GetVoteDoCount() {
    return 0;
  }

  protected function GetVotePollCount() {
    return 0;
  }
}
