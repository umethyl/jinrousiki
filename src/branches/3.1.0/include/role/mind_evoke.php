<?php
/*
  ◆口寄せ (mind_evoke)
  ○仕様
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_evoke extends Role_mind_read {
  //霊界遺言登録
  public function SaveHeavenLastWords($str) {
    //口寄せしているイタコすべての遺言を更新する
    foreach ($this->GetActor()->GetPartner($this->role) as $id) {
      $target = DB::$USER->ByID($id);
      if ($target->IsLive()) $target->Update('last_words', $str);
    }
  }
}
