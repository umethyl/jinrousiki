<?php
/*
  ◆恋人 (lovers)
  ○仕様
  ・役職表示：無し
  ・仲間表示：対象者 (憑依追跡 / 恋人系委託あり)
*/
class Role_lovers extends Role {
  protected function IgnoreImage() {
    return true;
  }

  protected function GetPartner() {
    return $this->GetLoversPartner();
  }

  //恋人表示 (委託あり)
  final protected function GetLoversPartner() {
    if ($this->CallParent('IgnoreGetLoversPartner')) return array();

    $this->SetStack($this->GetActor()->GetPartnerList());
    $stack = array();
    foreach (DB::$USER->Get() as $user) {
      if ($this->IsActor($user)) continue;
      if ($this->CallParent('IsLoversPartner', $user)) {
	$stack[] = $user->GetName(); //憑依追跡
      }
    }
    return array('partner_header' => $stack);
  }

  //恋人表示委託判定
  protected function IgnoreGetLoversPartner() {
    return false;
  }

  //恋人表示判定
  protected function IsLoversPartner(User $user) {
    $target = $this->GetStack(); //mix-in に注意
    return $user->IsPartner($this->role, $target) ||
      $this->GetActor()->IsPartner('fake_lovers',      $user->id) ||
      $this->GetActor()->IsPartner('dummy_chiroptera', $user->id) ||
      (DB::$ROOM->IsDate(1) && $user->IsPartner('sweet_status', $target));
  }

  protected function OutputPartnerByType(array $list, $type) {
    RoleHTML::OutputPartner($list, $type, 'lovers_footer');
  }

  //囁き (恋耳鳴)
  public function Whisper(TalkBuilder $builder, TalkParser $talk) {
    if (! $builder->flag->sweet_ringing) return false; //スキップ判定

    $str   = RoleTalkMessage::LOVERS_TALK;
    $voice = $talk->font_type;
    foreach ($builder->filter as $filter) {
      $filter->FilterWhisper($voice, $str); //フィルタリング処理
    }

    $stack = array(
      'str'       => $str,
      'symbol'    => '',
      'user_info' => RoleTalkMessage::LOVERS,
      'voice'     => $voice,
      'talk_id'   => $builder->GetTalkID($talk)
    );
    return $builder->Register($stack);
  }

  //後追い処理
  public function Followed($sudden_death = false, $not_kill = false) {
    $cupid_list      = array(); //キューピッドのID => 恋人のID
    $lost_cupid_list = array(); //恋人が死亡したキューピッドのリスト
    $checked_list    = array(); //処理済キューピッドのID
    $followed_list   = array(); //後追い恋人リスト
    $fox_list        = array(); //妖狐リスト
    $fox_live_list   = array(); //生存妖狐リスト
    $depraver_list   = array(); //背徳者リスト
    foreach (DB::$USER->Get() as $user) { //キューピッドと死んだ恋人のリストを取得
      foreach ($user->GetPartner($this->role, true) as $id) {
	$cupid_list[$id][] = $user->id;
	if ($user->IsOn(UserMode::DEAD) || $user->IsOn(UserMode::REVIVE)) {
	  $lost_cupid_list[$id] = $id;
	}
      }

      if (RoleUser::IsFoxCount($user)) {
	$fox_list[$user->id] = $user->id;
	if ($user->IsLive(true)) {
	  $fox_live_list[$user->id] = $user->id;
	}
      }

      if ($this->IsDepraver($user) && ! $user->IsDummyBoy()) {
	$depraver_list[$user->id] = $user->id;
      }
    }
    //Text::p($fox_list,      '◆List [fox]');
    //Text::p($fox_live_list, '◆List [fox/live]');
    //Text::p($depraver_list, '◆List [depraver]');

    if (count($fox_list) > 0 && count($depraver_list) > 0) { //背徳者出現判定
      $id = ArrayFilter::Pick($fox_list);
      $cupid_list[$id] = $depraver_list; //任意の妖狐をキューピッドの代理として設定しておく
      if (count($fox_live_list) < 1) {
	$lost_cupid_list[] = $id; //後追い判定
      }
    }
    //Text::p($cupid_list,      '◆List [cupid]');
    //Text::p($lost_cupid_list, '◆List [cupid/lost]');

    while (count($lost_cupid_list) > 0) { //対象キューピッドがいれば処理
      $cupid_id = array_shift($lost_cupid_list);
      $checked_list[] = $cupid_id;
      if (in_array($cupid_id, $fox_list)) { //背徳者後追い
	foreach ($depraver_list as $depraver_id) {
	  $user = DB::$USER->ByID($depraver_id); //背徳者の情報を取得
	  if ($not_kill) {
	    if (in_array($user->id, $followed_list)) continue;
	    $followed_list[] = $user->id;
	  } else {
	    if (! DB::$USER->Kill($user->id, DeadReason::FOX_FOLLOWED)) continue;
	    //突然死の処理
	    if ($sudden_death) DB::$ROOM->Talk($user->handle_name . DeadMessage::$fox_followed);
	    $user->Flag()->On(UserMode::SUICIDE);
	  }

	  foreach ($user->GetPartner($this->role, true) as $id) { //恋人連鎖後追い判定
	    $this->SetChainFollowed($id, $checked_list, $lost_cupid_list);
	  }
	}
      } else {
	foreach ($cupid_list[$cupid_id] as $lovers_id) { //恋人後追い
	  $user = DB::$USER->ByID($lovers_id); //恋人の情報を取得
	  if ($not_kill) {
	    if (in_array($user->id, $followed_list)) continue;
	    $followed_list[] = $user->id;
	  } else {
	    if (! DB::$USER->Kill($user->id, DeadReason::LOVERS_FOLLOWED)) continue;
	    //突然死の処理
	    if ($sudden_death) DB::$ROOM->Talk($user->handle_name . DeadMessage::$lovers_followed);
	    $user->Flag()->On(UserMode::SUICIDE);
	  }

	  foreach ($user->GetPartner($this->role) as $id) { //恋人連鎖後追い判定
	    $this->SetChainFollowed($id, $checked_list, $lost_cupid_list);
	  }

	  if (in_array($user->id, $fox_live_list)) { //妖狐死亡判定
	    unset($fox_live_list[$user->id]);
	    //Text::p($fox_live_list, '◆List [fox/live]');
	    if (count($fox_live_list) < 1) { //背徳者連鎖後追い判定
	      $id = ArrayFilter::Pick($fox_list);
	      $this->SetChainFollowed($id, $checked_list, $lost_cupid_list);
	    }
	  }
	}
      }
    }

    return $followed_list;
  }

  //後追い対象背徳者系判定
  private function IsDepraver(User $user) {
    if ($user->IsDead(true) || RoleUser::IsContainLovers($user)) return false;

    if (RoleUser::IsDelayCopy($user)) { //時間差コピー能力者ならコピー先を辿る
      $id = $user->GetMainRoleTarget();
      return is_null($id) ? false : DB::$USER->ByID($id)->IsMainGroup(CampGroup::DEPRAVER);
    } else {
      return $user->IsMainGroup(CampGroup::DEPRAVER);
    }
  }

  //連鎖後追い判定
  private function SetChainFollowed($id, array $list, array &$stack) {
    if (in_array($id, $list) || in_array($id, $stack)) return;
    $stack[] = $id;
  }
}
