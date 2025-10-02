<?php
/*
  ◆発言数制限制 (limit_talk)
*/
class Option_limit_talk extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::LIMIT_TALK;

  public function LoadPost() {
    RQ::Get()->ParsePostOn($this->name);
    if (! RQ::Get()->{$this->name}) {
      return false;
    }

    $post = sprintf('%s_count', $this->name);
    RQ::Get()->ParsePostInt($post);
    $count = RQ::Get()->$post;
    if ($count < 1) {
      RoomManagerHTML::OutputResult('limit_over', $this->GetName());
    }
    $this->Set(sprintf('%s:%d', $this->name, $count));
  }

  public function GetCaption() {
    return '発言数制限制';
  }

  public function GetExplain() {
    return '昼の発言数に制限がかかります';
  }

  protected function GetRoomCaption() {
    return parent::GetRoomCaption() . $this->GetRoomCaptionFooter();
  }

  public function GenerateImage() {
    $str = sprintf('[%d]', ArrayFilter::Pick($this->GetStack()));
    return ImageManager::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
  }

  public function GenerateRoomCaption() {
    $image   = $this->GenerateImage();
    $url     = $this->GetURL();
    $caption = parent::GetRoomCaption();
    $explain = $this->GetExplain() . $this->GetRoomCaptionFooter();
    return OptionHTML::GenerateRoomCaption($image, $url, $caption, $explain);
  }

  //村用キャプション追加メッセージ取得
  private function GetRoomCaptionFooter() {
    return sprintf(' (%d回)', ArrayFilter::Pick($this->GetStack()));
  }
}
