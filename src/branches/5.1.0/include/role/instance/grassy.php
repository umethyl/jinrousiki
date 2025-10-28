<?php
/*
  ◆草原迷彩 (grassy)
  ○仕様
  ・発言変換：草追加 (一文字毎/行頭以外)
*/
class Role_grassy extends Role {
  public function ConvertSay() {
    $result = '';
    foreach (Text::Split($this->GetStack('say')) as $str) {
      $result .= ($str == Text::LF ? $str : $str . 'w '); //改行判定
    }
    $this->SetStack($result, 'say');
  }
}
