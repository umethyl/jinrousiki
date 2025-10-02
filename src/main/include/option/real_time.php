<?php
/*
  ◆リアルタイム制 (real_time)
*/
class Option_real_time extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'realtime';

  function LoadPost() {
    RQ::Get()->ParsePostOn($this->name);
    if (RQ::Get()->{$this->name}) {
      RQ::Get()->ParsePostInt(sprintf('%s_day', $this->name), sprintf('%s_night', $this->name));
    }
    return RQ::Get()->{$this->name};
  }

  function GetCaption() { return 'リアルタイム制'; }

  function GetExplain() { return '制限時間が実時間で消費されます'; }

  function GenerateImage() {
    list($day, $night) = $this->GetStack();
    $str = sprintf('[%d：%d]', $day, $night);
    return Image::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
  }

  function GenerateRoomCaption() {
    $format  = '<div>%s：<a href="info/%s">%s</a>：%s</div>' . Text::LF;
    $image   = $this->GenerateImage();
    $url     = $this->GetURL();
    $caption = parent::GetRoomCaption();
    $explain = $this->GetExplain() . $this->GetRoomCaptionFooter();
    return sprintf($format, $image, $url, $caption, $explain);
  }

  protected function GetRoomCaption() {
    return parent::GetRoomCaption() . $this->GetRoomCaptionFooter();
  }

  //村用キャプション追加メッセージ取得
  private function GetRoomCaptionFooter() {
    list($day, $night) = $this->GetStack();
    return sprintf(' (昼： %d 分 / 夜： %d 分)', $day, $night);
  }
}
