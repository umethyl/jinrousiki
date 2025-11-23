<?php
//◆文字化け抑制◆//
//-- GamePlay 出力クラス (ゲーム終了後) --//
class GamePlayView_after extends GamePlayView {
  protected function OutputHeaderLogLink() {
    $this->OutputGameLogLinkList();
  }

  protected function OutputHeaderLinkHeader() {
    echo Text::BR; //ゲーム終了後は自動更新しない
  }

  protected function OutputHeaderLink() {
    $stack = [RequestDataGame::ICON, RequestDataGame::NAME, RequestDataGame::LIST];
    $this->OutputHeaderSwitchLink($stack);

    //別ページリンク
    GamePlayHTML::OutputHeaderLink('game_play', $this->SelectURL([RequestDataGame::LIST]));
    if (ServerConfig::DEBUG_MODE) { //観戦モードリンク
      GamePlayHTML::OutputHeaderLink('game_view', $this->SelectURL([]));
    }

    GameHTML::OutputLogLink();
  }

  protected function OutputGameLogLinkListFooter() {
    if (DateBorder::First()) {
      $this->OutputGameLogLink(RoomScene::DAY, DB::$ROOM->date);
    }

    if (TalkDB::ExistsLastNight()) {
      $this->OutputGameLogLink(RoomScene::NIGHT, DB::$ROOM->date);
    }

    $this->OutputGameLogLink(RoomScene::AFTER);
    $this->OutputGameLogLink(RoomScene::HEAVEN);
  }

  protected function OutputTimeTable() {
    Winner::Output();
  }

  protected function IgnoreSelfLastWords() {
    return true;
  }

  protected function IgnoreSound() {
    return true;
  }

  protected function EnableForm() {
    return false;
  }

  public function OutputAsync() {
    GamePlayHTML::OutputSceneAsync();
    $this->OutputTalk();
  }
}
