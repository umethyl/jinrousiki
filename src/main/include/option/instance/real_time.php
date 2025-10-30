<?php
/*
  ◆リアルタイム制 (real_time)
*/
class Option_real_time extends OptionLimitedCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::REALTIME;

  public function LoadPost() {
    RQ::Fetch()->ParsePostOn($this->name);
    if (false === RQ::Fetch()->{$this->name}) {
      return false;
    }

    $post_day   = sprintf('%s_day',   $this->name);
    $post_night = sprintf('%s_night', $this->name);
    RQ::Fetch()->ParsePostInt($post_day, $post_night);
    $day   = RQ::Fetch()->$post_day;
    $night = RQ::Fetch()->$post_night;
    if (Number::OutRange($day, 1, 99) || Number::OutRange($night, 1, 99)) {
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

  public function GenerateImage() {
    list($day, $night) = $this->GetStack();
    $str = sprintf('[%d：%d]', $day, $night);
    return ImageManager::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
  }

  protected function GetRoomCaptionFooter() {
    list($day, $night) = $this->GetStack();
    return $this->GetRoomCaptionConfig('昼： %d 分 / 夜： %d 分', $day, $night);
  }
}
