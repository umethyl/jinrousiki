<?php
/*
  ◆過去ログ一覧 (old_log)
  ○仕様
*/
class Request_old_log extends Request {
  public function __construct() {
    $this->ParseGetInt(RequestDataGame::DB, RequestDataGame::ID);
    $this->ParseGetOn(RequestDataLogRoom::WATCH);
    if ($this->room_no > 0) {
      $this->is_room = true;
      $this->ParseGetInt(
	RequestDataUser::ID,
	RequestDataLogRoom::SCROLL,
	RequestDataLogRoom::SCROLL_TIME
      );
      $this->ParseGetOn(
	RequestDataLogRoom::REVERSE_LOG,
	RequestDataLogRoom::HEAVEN,
	RequestDataLogRoom::HEAVEN_ONLY,
	RequestDataLogRoom::ADD_ROLE,
	RequestDataLogRoom::TIME,
	RequestDataGame::ICON,
	RequestDataLogRoom::SEX,
	RequestDataLogRoom::WOLF,
	RequestDataLogRoom::PERSONAL,
	RequestDataLogRoom::ROLE_LIST,
	RequestDataLogRoom::SCROLL_ON,
	RequestDataLogRoom::AUTO_PLAY,
	RoomMode::AUTO_PLAY
      );
    } else {
      $this->ParseGetData(
	RequestDataLogRoom::REVERSE_LIST,
	RequestDataLogRoom::NAME,
	RequestDataLogRoom::ROOM_NAME,
	RequestDataLogRoom::WINNER,
	RequestDataLogRoom::ROLE,
	RequestDataLogRoom::GAME_TYPE
      );
      $this->ParseGet('SetPage', 'page');
    }
  }

  //個別ログページのヘッダリンク生成
  public function GetURL() {
    $stack = $this->GetHeaderLinkList();
    $url   = '';
    foreach ($stack as $i => $name) {
      $base_url = URL::GetRoom('old_log', $this->room_no);
      foreach (array_keys($stack) as $j) {
	if ($j == $i) {
	  continue;
	}
	$base_url .= $this->ToURL($j);
      }
      $base_url .= ($this->$i ? '' : URL::AddSwitch($i)) . URL::AddDB();
      $link_url  = OldLogHTML::GenerateSwitchLink($base_url, $name, Switcher::Get($this->$i));
      $url .= Text::LineFeed($link_url);
    }

    return $url;
  }

  //個別ログページの自動スクロールリンク生成
  public function GetScrollURL() {
    $url = '';
    $base_url = URL::GetRoom('old_log', $this->room_no);
    foreach (array_keys($this->GetHeaderLinkList()) as $i) {
      switch ($i) {
      case RequestDataLogRoom::REVERSE_LOG:
	break;

      default:
	$base_url .= $this->ToURL($i);
	break;
      }
    }
    $base_url .= URL::AddSwitch(RequestDataLogRoom::REVERSE_LOG);
    $base_url .= URL::AddDB();
    foreach ($this->GetScrollLinkList() as $name => $list) {
      $link = $base_url;
      foreach ($list as $distance => $timeout) {
	$link .= URL::AddInt('scroll', $distance);
	$link .= URL::AddInt('scroll_time', $timeout);
      }
      $link_url = Text::QuoteBracket(LinkHTML::Generate($link, $name));
      $url .= Text::LineFeed($link_url);
    }
    return $url;
  }

  //個別ログページの自動再生リンク生成
  public function GetAutoPlayURL() {
    $url = '';
    $base_url = URL::GetRoom('old_log', $this->room_no);
    foreach (array_keys($this->GetHeaderLinkList()) as $i) {
      switch ($i) {
      case RequestDataLogRoom::REVERSE_LOG:
      case RequestDataLogRoom::WATCH:
	break;

      default:
	$base_url .= $this->ToURL($i);
	break;
      }
    }
    $base_url .= URL::AddSwitch(RequestDataLogRoom::WATCH);
    $base_url .= URL::AddSwitch(RequestDataLogRoom::TIME);
    $base_url .= URL::AddSwitch(RoomMode::AUTO_PLAY);
    $base_url .= URL::AddDB();
    foreach ($this->GetAutoPlayLinkList() as $name => $time) {
      $link = $base_url;
      $link .= URL::AddInt('scroll_time', $time);
      $link_url = Text::QuoteBracket(LinkHTML::Generate($link, $name));
      $url .= Text::LineFeed($link_url);
    }
    return $url;
  }

  //個別ログページの項目リスト取得
  private function GetHeaderLinkList() {
    return [
      RequestDataLogRoom::REVERSE_LOG	=> Message::LOG_REVERSE,
      RequestDataLogRoom::HEAVEN	=> Message::LOG_DEAD,
      RequestDataLogRoom::HEAVEN_ONLY	=> Message::LOG_HEAVEN,
      RequestDataLogRoom::ADD_ROLE	=> OldLogMessage::ROLE,
      RequestDataLogRoom::TIME		=> OldLogMessage::TIME,
      RequestDataGame::ICON		=> OldLogMessage::ICON,
      RequestDataLogRoom::SEX		=> OldLogMessage::SEX,
      RequestDataLogRoom::WATCH		=> Message::LOG_WATCH,
      RequestDataLogRoom::WOLF		=> OldLogMessage::WOLF,
      RequestDataLogRoom::PERSONAL	=> OldLogMessage::PERSONAL,
      RequestDataLogRoom::SCROLL_ON	=> OldLogMessage::SCROLL,
      RequestDataLogRoom::AUTO_PLAY	=> OldLogMessage::AUTO
    ];
  }

  //自動スクロール項目リスト取得
  private function GetScrollLinkList() {
    return [
      'A1' => [  1 =>   30],
      'A2' => [  1 =>   10],
      'A3' => [  3 =>   10],
      'B1' => [ 10 =>  500],
      'B2' => [ 50 =>  500],
      'B3' => [100 =>  500],
      'C1' => [100 => 1000],
      'C2' => [200 => 1000],
      'C3' => [300 => 1000]
    ];
  }

  //自動再生項目リスト取得
  private function GetAutoPlayLinkList() {
    return [
      '等速' =>  1,
       '2倍' =>  2,
       '3倍' =>  3,
       '5倍' =>  5,
      '10倍' => 10,
      '20倍' => 20,
      '50倍' => 50
    ];
  }
}
