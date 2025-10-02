<?php
/*
  ◆沈黙禁止 (no_silence)
  ○仕様
*/
class Option_no_silence extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '沈黙禁止';
  }

  public function GetExplain() {
    return '昼に一度も発言がない人を処刑投票処理時に突然死させます';
  }

  //沈黙死処理
  public function SilenceKill() {
    //発言数取得
    if (DB::$ROOM->IsTest()) {
      $stack = RQ::GetTest()->talk_count;
    } else {
      //スキップ判定 (超過前 or 未投票発言者あり)
      if (GameTime::IsInTime() || TalkDB::CountNoVoteTalker() > 0) return;
      $stack = TalkDB::GetAllUserTalkCount();
    }
    //Text::p($stack, "◆TalkCount [{$this->name}]");

    foreach (DB::$USER->SearchLive() as $id => $name) {
      if (ArrayFilter::GetInt($stack, $id) > 0) continue;
      DB::$USER->SuddenDeath($id, DeadReason::SILENCE);
    }
  }

  //沈黙死者数取得
  public function CountSilence() {
    $count = 0;
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsOn(UserMode::SUICIDE) && $user->ExistsVote()) { //沈黙死投票者判定
	DB::$ROOM->Stack()->DeleteKey('vote', $user->id);
	$count++;
      }
    }
    //Text::p($count, "◆Count [{$this->name}]");

    return $count;
  }
}
