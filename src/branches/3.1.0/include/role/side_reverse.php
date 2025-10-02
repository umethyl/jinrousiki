<?php
/*
  ◆鏡面迷彩 (side_reverse)
  ○仕様
  ・発言変換：左右置換 (行単位)
*/
class Role_side_reverse extends Role {
  public function ConvertSay() {
    $result = '';
    $line   = array();
    foreach (Text::Split($this->GetStack('say')) as $str) {
      if ($str == Text::LF) {
	if (count($line) > 0) $result .= ArrayFilter::ConcatReverse($line, '');
	$result .= $str;
	$line = array();
      } else {
	$line[] = $str;
      }
    }
    if (count($line) > 0) $result .= ArrayFilter::ConcatReverse($line, '');
    $this->SetStack($result, 'say');
  }
}
