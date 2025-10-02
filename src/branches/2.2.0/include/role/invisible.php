<?php
/*
  ◆光学迷彩 (invisible)
  ○仕様
  ・発言変換：消滅 (割合はサーバ設定)
    - 判定は一文字毎で、空白、タブ、改行文字は対象外
*/
class Role_invisible extends Role {
  function ConvertSay() {
    $say    = $this->GetStack('say');
    $result = '';
    $regex  = "/[\t\r\n\s]/";
    $count  = mb_strlen($say);
    $stack  = range(0, $count);
    shuffle($stack);
    $target_stack = array_slice($stack, 0, ceil($count * GameConfig::INVISIBLE_RATE / 100));
    for ($i = 0; $i < $count; $i++) {
      $str = mb_substr($say, $i, 1);
      if (preg_match($regex, $str)) {
	$result .= $str;
      }
      elseif (in_array($i, $target_stack)) {
	$result .= (strlen($str) == 2 ? '　' : '&nbsp;');
      }
      else {
	$result .= $str;
      }
    }
    $this->SetStack($result, 'say');
  }
}
