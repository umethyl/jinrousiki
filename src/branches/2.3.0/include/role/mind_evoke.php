<?php
/*
  ◆口寄せ (mind_evoke)
  ○仕様
  ・表示：2 日目以降
*/
class Role_mind_evoke extends Role {
  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  //霊界遺言登録
  public function SaveHeavenLastWords($str) {
    //口寄せしているイタコすべての遺言を更新する
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      $target = DB::$USER->ByID($id);
      if ($target->IsLive()) $target->Update('last_words', $str);
    }
  }
}
