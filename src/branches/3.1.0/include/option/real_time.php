<?php
/*
  ◆リアルタイム制 (real_time)
*/
class Option_real_time extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::REALTIME;

  public function LoadPost() {
    RQ::Get()->ParsePostOn($this->name);
    if (! RQ::Get()->{$this->name}) return false;

    $post_day   = sprintf('%s_day',   $this->name);
    $post_night = sprintf('%s_night', $this->name);
    RQ::Get()->ParsePostInt($post_day, $post_night);
    $day   = RQ::Get()->$post_day;
    $night = RQ::Get()->$post_night;
    if ($day < 1 || 99 < $day || $night < 1 || 99 < $night) {
      RoomManagerHTML::OutputResult('time');
    }
    $this->Set(sprintf('%s:%d:%d', $this->name, $day, $night));
  }

  public function GetCaption() {
    return 'リアルタイム制';
  }

  public function GetExplain() {
    return '制限時間が実時間で消費されます';
  }

  protected function GetRoomCaption() {
    return parent::GetRoomCaption() . $this->GetRoomCaptionFooter();
  }

  public function GenerateImage() {
    list($day, $night) = $this->GetStack();
    $str = sprintf('[%d：%d]', $day, $night);
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
    list($day, $night) = $this->GetStack();
    return sprintf(' (昼： %d 分 / 夜： %d 分)', $day, $night);
  }
}
