<?php
/*
  ◆吠耳鳴 (howl_ringing)
  ○仕様
*/
class Role_howl_ringing extends Role {
  public $mix_in = 'wolf';

  function Whisper(TalkBuilder $builder, $voice) {
    return $builder->flag->{$this->role} && $this->Howl($builder, $voice);
  }
}
