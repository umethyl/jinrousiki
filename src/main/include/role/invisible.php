<?php
/*
  ◆光学迷彩 (invisible)
  ○仕様
  ・自分の発言の一部が一定確率で消える
  ・判定は一文字毎で、空白、タブ、改行文字は対象外
  ・確率の初期値は GameConfig->invisible_rate で定義し, 一文字毎に 1% アップする
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_invisible extends Role{
  function Role_invisible(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    global $GAME_CONF;

    $result = '';
    $regex  = "/[\t\r\n 　]/";
    $rate   = $GAME_CONF->invisible_rate;
    $count  = mb_strlen($sentence);
    for($i = 0; $i < $count; $i++){
      $str = mb_substr($sentence, $i, 1);
      if(preg_match($regex, $str)){
	$result .= $str;
	continue;
      }

      if(mt_rand(1, 100) <= $rate)
	$result .= (strlen($str) == 2 ? '　' : '&nbsp;');
      else
	$result .= $str;
      if(++$rate > 100) break;
    }
    $sentence = $result;
  }
}
