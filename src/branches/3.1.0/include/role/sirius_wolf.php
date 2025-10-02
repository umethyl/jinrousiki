<?php
/*
  ◆天狼 (sirius_wolf)
  ○仕様
  ・能力結果：耐性通知
*/
RoleLoader::LoadFile('wolf');
class Role_sirius_wolf extends Role_wolf {
  protected function OutputAddResult() {
    $stack = $this->GetStack();
    //未定義なら取り直す (日数判定はログ対策)
    if (is_null($stack) || ! DB::$ROOM->IsDate($stack['date'])) {
      $stack = $this->GetAbilitySiriusWolf();
    }

    if ($stack['full']) {
      RoleHTML::OutputAbilityResult('ability_full_sirius_wolf', null);
    } elseif ($stack[Switcher::ON]) {
      RoleHTML::OutputAbilityResult('ability_sirius_wolf', null);
    }
  }

  //覚醒状態取得
  public function GetAbilitySiriusWolf() {
    //生存人狼数依存なので日数単位で同一になるはず (日数を追加しているのはログ対策)
    $stack = array();
    $count = DB::$USER->CountLiveWolf();

    $stack[Switcher::ON]	= $count < 3;;
    $stack['full']		= $count == 1;
    $stack['date']		= DB::$ROOM->date;
    $this->SetStack($stack);

    return $stack;
  }
}
