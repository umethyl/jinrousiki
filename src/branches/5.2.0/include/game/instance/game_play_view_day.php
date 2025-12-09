<?php
//◆文字化け抑制◆//
//-- GamePlay 出力クラス (昼) --//
class GamePlayView_day extends GamePlayView {
  protected function OutputGameLogLinkListHeader() {
    GameHTML::OutputGameLogLinkListHeader();
  }

  protected function OutputHeaderLinkFooter() {
    $this->OutputGameLogLinkList();
  }

  protected function OutputSoundScene() {
    if (JinrouCookie::$scene != '' && JinrouCookie::$scene != DB::$ROOM->scene) { //夜明け
      SoundHTML::Output('morning');
    }

    if (JinrouCookie::$vote_result == DB::$ROOM->scene) { //投票完了
      SoundHTML::Output('vote_success');
    }
  }
}
