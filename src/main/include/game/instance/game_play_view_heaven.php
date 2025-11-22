<?php
//◆文字化け抑制◆//
//-- GamePlay 出力クラス (霊界) --//
class GamePlayView_heaven extends GamePlayView {
  protected function IgnoreOutput() {
    return false === DB::$SELF->IsDead();
  }

  protected function EnableGamePlay() {
    return false;
  }

  protected function OutputHeaderTitle() {
    TableHTML::OutputTd(GamePlayMessage::HEAVEN_TITLE);
  }

  protected function OutputHeaderLinkHeader() {
    return;
  }

  protected function OutputHeaderLink() {
    return;
  }

  protected function IgnoreObjection($left_time) {
    return true;
  }

  protected function OutputTalk() {
    if (JinrouCacheManager::Enable(JinrouCacheManager::TALK_HEAVEN)) {
      $filter = JinrouCacheManager::Get(JinrouCacheManager::TALK_HEAVEN);
    } else {
      $filter = Talk::FetchHeaven();
    }
    $filter->Output();
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
    if (false === DB::$SELF->IsDead()) {
      return;
    }

    GamePlayHTML::OutputSceneAsync();
    $this->OutputTalk();
  }
}
