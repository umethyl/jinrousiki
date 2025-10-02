<?php
/*
  ◆大声 (strong_voice)
  ○仕様
  ・声量変換：「大声」固定 (ゲームプレイ中・生存時 / 呼び出し側で対応)
*/
class Role_strong_voice extends Role {
  public $voice_list = array('weak', 'normal', 'strong');

  //声量変換
  function FilterVoice(&$voice, &$str) {
    $voice = array_shift(explode('_', $this->role));
  }

  //声量シフト
  function ShiftVoice(&$voice, &$str, $up = true) {
    if (($key = array_search($voice, $this->voice_list)) === false) return;
    if ($up) {
      if (++$key >= count($this->voice_list)) {
	$str = Message::$howling;
	return;
      }
    }
    else {
      if (--$key < 0) {
	$str = Message::$common_talk;
	return;
      }
    }
    $voice = $this->voice_list[$key];
  }
}
