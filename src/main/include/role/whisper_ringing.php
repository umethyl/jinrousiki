<?php
/*
  ◆囁耳鳴 (whisper_ringing)
  ○仕様
*/
class Role_whisper_ringing extends Role {
  public $mix_in = array('common');

  public function Whisper(TalkBuilder $builder, $voice) {
    return $builder->flag->{$this->role} && $this->CommonWhisper($builder, $voice);
  }
}
