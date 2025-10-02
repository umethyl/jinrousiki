<?php
/*
  ◆リアルタイム制 (real_time)
*/
class Option_real_time extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'realtime';

  public function LoadPost() {
    RQ::Get()->ParsePostOn($this->name);
    if (RQ::Get()->{$this->name}) {
      RQ::Get()->ParsePostInt(sprintf('%s_day', $this->name), sprintf('%s_night', $this->name));
    }
    return RQ::Get()->{$this->name};
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
    return Image::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
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
