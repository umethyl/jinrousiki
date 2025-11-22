<?php
//◆文字化け抑制◆//
//-- GamePlay 出力クラス (ゲーム開始前) --//
class GamePlayView_before extends GamePlayView {
  protected function OutputHeaderLinkFooter() {
    $url = sprintf('%s&user_no=%s', $this->SelectURL([]), DB::$SELF->id);
    GamePlayHTML::OutputHeaderLink('user_manager', $url); //登録情報変更
    if (RoomOptionManager::EnableChange()) { //村オプション変更
      GamePlayHTML::OutputHeaderLink('room_manager', $this->SelectURL([]));
    }
  }

  protected function OutputTimeTableHeader() {
    GamePlayHTML::OutputHeaderCaution();
    RoomOptionLoader::Output();
  }

  protected function IgnoreObjection($left_time) {
    return false;
  }

  protected function IgnoreTimelimit() {
    return true;
  }

  protected function OutputSoundHeader() {
    if (JinrouCookie::$user_count > 0) { //人数変動
      $user_count = DB::$USER->Count();
      $max_user   = RoomDB::Get('max_user');
      if ($user_count == $max_user && JinrouCookie::$user_count != $max_user) { //満員
	SoundHTML::Output('full');
      } elseif (JinrouCookie::$user_count != $user_count) { //入村
	SoundHTML::Output('entry');
      }
    }
  }

  protected function OutputSoundScene() {
    if (JinrouCookie::$vote_result == DB::$ROOM->scene) { //投票完了
      SoundHTML::Output('vote_success');
    }
  }

  protected function EnableForm() {
    return false;
  }

  public function OutputAsync() {
    GamePlayHTML::OutputSceneAsync();
    GameHTML::OutputPlayer();
    $this->OutputTalk();
    if (RQ::Enable('play_sound')) {
      $this->OutputSound();
    }
  }
}
