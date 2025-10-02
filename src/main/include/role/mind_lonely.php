<?php
/*
  ◆はぐれ者 (mind_lonely)
  ○仕様
*/
class Role_mind_lonely extends Role {
  public $mix_in = 'silver_wolf';

  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  function Whisper(TalkBuilder $builder, $voice) {
    return $this->GetActor()->IsWolf() && parent::Whisper($builder, $voice);
  }
}
