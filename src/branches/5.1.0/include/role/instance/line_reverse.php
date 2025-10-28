<?php
/*
  ◆天地迷彩 (line_reverse)
  ○仕様
  ・発言変換：上下置換 (行単位)

  ○問題点
  ・最後が改行だった場合はカットされる (explode + implode の仕様)
*/
class Role_line_reverse extends Role {
  public function ConvertSay() {
    $str = ArrayFilter::ConcatReverse(Text::Parse($this->GetStack('say'), Text::LF), Text::LF);
    $this->SetStack($str, 'say');
  }
}
