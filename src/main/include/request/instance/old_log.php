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
    $stack = [
      RequestDataLogRoom::REVERSE_LOG	=> Message::LOG_REVERSE,
      RequestDataLogRoom::HEAVEN	=> Message::LOG_DEAD,
      RequestDataLogRoom::HEAVEN_ONLY	=> Message::LOG_HEAVEN,
      RequestDataLogRoom::ADD_ROLE	=> OldLogMessage::ROLE,
      RequestDataLogRoom::TIME		=> OldLogMessage::TIME,
      RequestDataGame::ICON		=> OldLogMessage::ICON,
      RequestDataLogRoom::SEX		=> OldLogMessage::SEX,
      RequestDataLogRoom::WATCH		=> Message::LOG_WATCH,
      RequestDataLogRoom::WOLF		=> OldLogMessage::WOLF,
      RequestDataLogRoom::PERSONAL	=> OldLogMessage::PERSONAL
    ];
    $url = '';
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
}
