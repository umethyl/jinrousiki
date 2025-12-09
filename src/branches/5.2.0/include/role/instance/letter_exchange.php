<?php
/*
  ◆交換日記 (letter_exchange)
  ○仕様
  ・表示：1 日目, 所持日限定
  ・役職表示：当時所持の有無で入れ替え
*/
class Role_letter_exchange extends Role {
  protected function IgnoreAbility() {
    return DateBorder::Third() && false === $this->IsDoom();
  }

  protected function GetImage() {
    return $this->IsDoom() ? $this->role . '_today' : $this->role;
  }

  //遺言更新
  public function UpdateLastWords() {
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsDead(true) || false === $user->IsDoomRole($this->role)) {
	continue;
      }

      $target = $this->GetTargetLoversPartner($user);
      $this->StoreLastWords($target, $user->id);
      if (false === RoleUser::LimitedLastWords($target)) {
	$target->AddDoom(1, $this->role);
	DB::$ROOM->StoreDead($target->handle_name, DeadReason::LETTER_EXCHANGE_MOVED);
      }
    }
  }

  //恋人取得
  private function GetTargetLoversPartner(User $target) {
    $id = ArrayFilter::Pick($target->GetPartner('lovers', true)); //キューピッドのID
    foreach (DB::$USER->GetRoleUser('lovers') as $user) {
      if (false === $user->IsSame($target) && $user->IsPartner('lovers', $id)) {
	return $user;
      }
    }
  }

  //遺言登録
  private function StoreLastWords(User $user, $id) {
    $str = DB::$ROOM->IsTest() ? $user->uname : UserDB::GetLastWords($id);
    if (null === $str) {
      return true;
    }
    $user->Update('last_words', $str);
  }
}
