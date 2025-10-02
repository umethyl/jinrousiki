<?php
/*
  ◆囁耳鳴 (whisper_ringing)
  ○仕様
*/
class Role_whisper_ringing extends Role{
  public $mix_in = 'common';
  function __construct(){ parent::__construct(); }

  function Whisper($builder, $voice){
    return $builder->flag->{$this->role} && $this->filter->Whisper($builder, $voice);
  }
}
