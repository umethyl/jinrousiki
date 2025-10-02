<?php
/*
  ◆恋人 (lovers)
  ○仕様
*/
class Role_lovers extends Role {
  protected function OutputImage() { return; }

  protected function OutputPartner() {
    $target = $this->GetActor()->partner_list;
    $stack  = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user->uname)) continue;
      //夢求愛者・悲恋対応
      if ($user->IsPartner($this->role, $target) ||
	  $this->GetActor()->IsPartner('dummy_chiroptera', $user->user_no) ||
	  (DB::$ROOM->date == 1 && $user->IsPartner('sweet_status', $target))) {
	$stack[] = DB::$USER->GetHandleName($user->uname, true); //憑依追跡
      }
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'lovers_footer');
  }

  //囁き (恋耳鳴)
  function Whisper(TalkBuilder $builder, $voice) {
    if (! $builder->flag->sweet_ringing) return false; //スキップ判定
    $str = Message::$lovers_talk;
    foreach ($builder->filter as $filter) $filter->FilterWhisper($voice, $str); //フィルタリング処理
    $builder->AddRaw('', '恋人の囁き', $str, $voice);
    return true;
  }

  //後追い処理
  final function Followed($sudden_death = false) {
    $cupid_list      = array(); //キューピッドのID => 恋人のID
    $lost_cupid_list = array(); //恋人が死亡したキューピッドのリスト
    $checked_list    = array(); //処理済キューピッドのID

    foreach (DB::$USER->rows as $user) { //キューピッドと死んだ恋人のリストを作成
      foreach ($user->GetPartner($this->role, true) as $id) {
	$cupid_list[$id][] = $user->user_no;
	if ($user->dead_flag || $user->revive_flag) $lost_cupid_list[$id] = $id;
      }
    }

    while (count($lost_cupid_list) > 0) { //対象キューピッドがいれば処理
      $cupid_id = array_shift($lost_cupid_list);
      $checked_list[] = $cupid_id;
      foreach ($cupid_list[$cupid_id] as $lovers_id) { //キューピッドのリストから恋人の ID を取得
	$user = DB::$USER->ById($lovers_id); //恋人の情報を取得
	if (! DB::$USER->Kill($user->user_no, 'LOVERS_FOLLOWED')) continue;
	//突然死の処理
	if ($sudden_death) DB::$ROOM->Talk($user->handle_name . Message::$lovers_followed);
	$user->suicide_flag = true;

	foreach ($user->GetPartner($this->role) as $id) { //後追いした恋人のキューピッドのIDを取得
	  if (! (in_array($id, $checked_list) || in_array($id, $lost_cupid_list))) { //連鎖判定
	    $lost_cupid_list[] = $id;
	  }
	}
      }
    }
  }
}
