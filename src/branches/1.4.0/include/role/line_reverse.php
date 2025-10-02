<?php
/*
  ◆天地迷彩 (line_reverse)
  ○仕様
  ・自分の発言が行単位で上下が入れ替わる
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)

  ○問題点
  ・最後が改行だった場合はカットされる (explode + implode の仕様)
*/
class Role_line_reverse extends Role{
  function Role_line_reverse(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    $sentence = implode("\n", array_reverse(explode("\n", $sentence)));
  }
}
