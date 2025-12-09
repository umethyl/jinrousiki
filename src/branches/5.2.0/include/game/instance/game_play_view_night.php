<?php
//◆文字化け抑制◆//
//-- GamePlay 出力クラス (夜) --//
class GamePlayView_night extends GamePlayView {
  protected function OutputGameLogLinkListHeader() {
    GameHTML::OutputGameLogLinkListHeader();
  }

  protected function OutputHeaderLinkFooter() {
    $this->OutputGameLogLinkList();
  }

  protected function OutputSoundScene() {
    if (JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //日没
      SoundHTML::Output('night');
    }
  }

  protected function IgnoreSoundObjection() {
    return true;
  }
}
