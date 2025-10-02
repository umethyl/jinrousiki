<?php
/*
  ◆大声 (strong_voice)
  ○仕様
  ・声量変換：「大声」固定 (ゲームプレイ中・生存時 / 呼び出し側で対応)
*/
class Role_strong_voice extends Role {
  public $voice_list = [TalkVoice::WEAK, TalkVoice::NORMAL, TalkVoice::STRONG];

  //声量変換
  public function FilterVoice(&$voice, &$str) {
    $voice = Text::Cut($this->role, '_', null, false);
  }

  //声量シフト
  protected function ShiftVoice(&$voice, &$str, $up = true) {
    if (($key = array_search($voice, $this->voice_list)) === false) return;
    if ($up) {
      if (++$key >= count($this->voice_list)) {
	$str = RoleTalkMessage::HOWLING;
	return;
      }
    } else {
      if (--$key < 0) {
	$str = RoleTalkMessage::COMMON_TALK;
	return;
      }
    }
    $voice = $this->voice_list[$key];
  }
}
