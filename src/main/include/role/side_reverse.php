<?php
/*
  ◆鏡面迷彩 (side_reverse)
  ○仕様
  ・自分の発言が行単位で左右が入れ替わる
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_side_reverse extends Role{
  function Role_side_reverse(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    $result = '';
    $line = array();
    $count = mb_strlen($sentence);
    for($i = 0; $i < $count; $i++){
      $str = mb_substr($sentence, $i, 1);
      if($str == "\n"){
	if(count($line) > 0) $result .= implode('', array_reverse($line));
	$result .= $str;
	$line = array();
      }
      else{
	$line[] = $str;
      }
    }
    if(count($line) > 0) $result .= implode('', array_reverse($line));
    $sentence = $result;
  }
}
