<?php
/*
  ◆呪物遣い (fetish_cat)
  ○仕様
  ・蘇生率：60% / 誤爆率：0%
  ・蘇生後：蘇生身代わり登録
  ・蘇生身代わり：誤爆蘇生＆身代わり死
*/
RoleLoader::LoadFile('poison_cat');
class Role_fetish_cat extends Role_poison_cat {
  protected function GetReviveRate() {
    return 60;
  }

  protected function GetMissfireRate($revive) {
    return 0;
  }

  protected function ReviveAction() {
    $this->AddStack($this->GetActor());
  }

  //蘇生身代わり処理
  public function ReviveSacrifice() {
    $user_list = $this->GetStack();
    //天候判定 (「雷雨」発生時は空になる)
    if (count($user_list) < 1 || DB::$ROOM->IsEvent('no_sacrifice')) {
      return;
    }

    //誤爆蘇生とその身代わり候補をリストアップ
    $revive    = [];
    $sacrifice = [];
    foreach (DB::$USER->Get() as $target) {
      //身代わり君は常時対象外
      if ($target->IsDummyBoy()) {
	continue;
      }

      //現在生存しているなら身代わり候補枠
      if ($target->IsLive(true) || DB::$USER->IsVirtualLive($target->id, true)) {
	//蘇生者・特殊耐性は除外
	if ($target->IsOff(UserMode::REVIVE) && false === RoleUser::Avoid($target)) {
	  $sacrifice[] = $target->id;
	}
      } else {
	//蘇生制限対象者は除外
	if (false === RoleUser::LimitedRevive($target)) {
	  $revive[] = $target->id;
	}
      }
    }
    //Text::p($revive, "◆Revive [{$this->role}]");
    //Text::p($sacrifice, "◆Sacrifice [{$this->role}]");
    shuffle($revive);
    shuffle($sacrifice);

    shuffle($user_list); //途中で対象者が居なくなるケースがあるのでランダム化
    foreach ($user_list as $user) {
      if (count($revive) > 0) {
	$target = DB::$USER->ByID(array_pop($revive));
	$this->ReviveUser($target);
	DB::$ROOM->StoreAbility(RoleAbility::REVIVE, 'success', $target->handle_name, $user->id);

	if (count($sacrifice) > 0) {
	  DB::$USER->Kill(array_pop($sacrifice), DeadReason::SACRIFICE);
	}
      }
    }
  }
}
