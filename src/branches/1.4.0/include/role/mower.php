<?php
/*
  ◆草刈り (mower)
  ○仕様
  ・自分の発言から草が消える
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_mower extends Role{
  function Role_mower(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    $sentence = strtr($sentence, array('w' => '', 'ｗ' => '', 'W' => '', 'Ｗ' => ''));
  }
}
