<?php
/*
  ◆はぐれ者 (mind_lonely)
  ○仕様
  ・表示：2 日目以降
*/
class Role_mind_lonely extends Role {
  public $mix_in = 'silver_wolf';

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  public function Whisper(TalkBuilder $builder, $voice) {
    return $this->GetActor()->IsWolf() && $this->filter->Whisper($builder, $voice);
  }
}
