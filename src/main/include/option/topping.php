<?php
/*
  ◆固定配役追加モード (topping)
*/
class Option_topping extends OptionSelector {
  protected function LoadFormList() {
    $this->form_list = GameOptionConfig::${$this->source};
  }

  protected function LoadValue() {
    if (OptionManager::IsChange() && DB::$ROOM->IsOption($this->name)) {
      $this->value = DB::$ROOM->option_role->list[$this->name][0];
    }
  }

  public function LoadPost() {
    RQ::Get()->ParsePostData($this->name);
    if (is_null(RQ::Get()->{$this->name})) {
      return false;
    }

    $post = RQ::Get()->{$this->name};
    $flag = (false === empty($post)) && isset($this->form_list[$post]);
    if (true === $flag) {
      array_push(RoomOption::${$this->group}, sprintf('%s:%s', $this->name, $post));
    }
    RQ::Set($this->name, $flag);
  }

  public function GetCaption() {
    return '固定配役追加モード';
  }

  public function GetExplain() {
    return '固定配役に追加する役職セットです';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }

  public function GenerateImage() {
    $str = $this->GetRoomImageFooter();
    return ImageManager::Room()->Generate($this->name, $this->GetRoomCaption()) . $str;
  }

  public function GenerateRoomCaption() {
    $image   = $this->GenerateImage();
    $url     = sprintf('%s_%s', $this->GetURL(), $this->GetRoomType());
    $caption = $this->GetCaption() . $this->GetRoomImageFooter();
    $explain = $this->GetExplain() . $this->GetRoomCaptionFooter();
    return OptionHTML::GenerateRoomCaption($image, $url, $caption, $explain);
  }

  //村用個別オプション取得
  protected function GetRoomType() {
    return ArrayFilter::Pick($this->GetStack());
  }

  protected function GetRoomCaptionFooter() {
    $item = $this->GetItem();
    return $this->GetRoomCaptionConfig('Type%s', $item[$this->GetRoomType()]);
  }

  //村用画像追加メッセージ取得
  protected function GetRoomImageFooter() {
    return Text::QuoteBracket(strtoupper($this->GetRoomType()));
  }

  //闇鍋固定枠追加
  public function FilterChaosFixRole(array &$list) {
    $stack = DB::$ROOM->GetOptionList($this->name);
    if (count($stack) < 1) {
      return;
    }
    //Text::p($stack, '◆topping');

    if (ArrayFilter::IsAssoc($stack, 'fix')) { //固定枠
      foreach ($stack['fix'] as $role => $count) {
	ArrayFilter::Add($list, $role, $count);
	OptionManager::StoreDummyBoyCastLimit([$role]);
      }
    }

    if (ArrayFilter::IsAssoc($stack, 'random')) { //ランダム枠
      foreach ($stack['random'] as $key => $rate) {
	$result_list = Lottery::Add($list, Lottery::Generate($rate), $stack['count'][$key]);
	OptionManager::StoreDummyBoyCastLimit($result_list);
      }
    }
    //Text::p($list, sprintf('◆topping(%d)', array_sum($list)));
  }
}
