<?php
/*
  ◆曼陀羅華 (follow_chiroptera)
  ○仕様
  ・処刑：道連れショック死 (進行中の死の宣告対象者)
*/
class Role_follow_chiroptera extends Role {
  public $mix_in = ['chicken'];

  public function VoteKillCounter(array $list) {
    /*
      - 処理順の関係で処刑者の死亡処理は実行済みなので本人判定は行わない
      - 仕様上、特殊耐性恋人に憑依することはないので、メイン役職のみで回避判定を行う
      - 発動当日は相手側の能力でショック死することになるが、
        死因が変わるのでこちら側の能力発動対象とする
      - サブは憑依を追跡して判定する
    */
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsDead(true) || RoleUser::Avoid($user)) {
	continue;
      }

      if ($this->IsFollowMain($user) || $this->IsFollowSub($user->GetVirtual())) {
	$this->SuddenDeathKill($user->GetID());
      }
    }
  }

  //道連れ判定 (メイン役職)
  private function IsFollowMain(User $user) {
    //蝉蝙蝠
    $role = 'doom_chiroptera';
    if (true !== $user->IsRole($role)) {
      return false;
    }
    return DateBorder::InFuture(7);
  }

  //道連れ判定 (サブ役職)
  private function IsFollowSub(User $user) {
    //死の宣告
    $role = 'death_warrant';
    if (true !== $user->IsRole($role)) {
      return false;
    }
    return DateBorder::InFuture($user->GetDoomDate($role));
  }

  protected function GetSuddenDeathType() {
    return 'FOLLOWED';
  }
}
