<?php
/*
  ◆囁耳鳴 (whisper_ringing)
  ○仕様
*/
class Role_whisper_ringing extends Role {
  public $mix_in = 'common';

  function Whisper(TalkBuilder $builder, $voice) {
    return $builder->flag->{$this->role} && parent::Whisper($builder, $voice);
  }
}
