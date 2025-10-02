<?php
/*
  ◆草原迷彩 (grassy)
  ○仕様
  ・自分の発言の一文字毎に草がつく
  ・改行の後 (行頭) にはつけない
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_grassy extends Role{
  function Role_grassy(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    $result = '';
    $count = mb_strlen($sentence);
    for($i = 0; $i < $count; $i++){
      $str = mb_substr($sentence, $i, 1);
      $result .= ($str == "\n" ? $str : $str . 'w ');
    }
    $sentence = $result;
  }
}
