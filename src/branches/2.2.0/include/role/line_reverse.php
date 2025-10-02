<?php
/*
  ◆天地迷彩 (line_reverse)
  ○仕様
  ・発言変換：上下置換 (行単位)

  ○問題点
  ・最後が改行だった場合はカットされる (explode + implode の仕様)
*/
class Role_line_reverse extends Role {
  function ConvertSay() {
    $str = implode(Text::LF, array_reverse(explode(Text::LF, $this->GetStack('say'))));
    $this->SetStack($str, 'say');
  }
}
