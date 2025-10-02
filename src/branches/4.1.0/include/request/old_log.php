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
      $this->ParseGetInt(RequestDataUser::ID, 'scroll', 'scroll_time');
      $this->ParseGetOn(
	RequestDataLogRoom::REVERSE, RequestDataLogRoom::HEAVEN, RequestDataLogRoom::HEAVEN_ONLY,
	RequestDataLogRoom::ROLE,    RequestDataLogRoom::TIME,   RequestDataGame::ICON,
	RequestDataLogRoom::SEX,     RequestDataLogRoom::WOLF,   RequestDataLogRoom::PERSONAL,
	'role_list', 'auto_play'
      );
    } else {
      $this->ParseGetData('reverse', 'name', 'room_name', 'winner', 'role');
      $this->ParseGet('SetPage', 'page');
    }
  }

  //個別ログページのヘッダリンク生成
  public function GetURL() {
    $stack = [
      RequestDataLogRoom::REVERSE	=> '逆',
      RequestDataLogRoom::HEAVEN	=> '霊',
      RequestDataLogRoom::HEAVEN_ONLY	=> '逝',
      RequestDataLogRoom::ROLE		=> '役',
      RequestDataLogRoom::TIME		=> '時',
      RequestDataGame::ICON		=> '顔',
      //RequestDataLogRoom::SEX		=> '性',
      RequestDataLogRoom::WATCH		=> '観',
      RequestDataLogRoom::WOLF		=> '狼',
      RequestDataLogRoom::PERSONAL	=> '結'
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
      $base_url .= ($this->$i ? '' : URL::GetSwitch($i)) . URL::GetAddDB();
      $link_url  = OldLogHTML::GenerateSwitchLink($base_url, $name, Switcher::Get($this->$i));
      $url .= Text::LineFeed($link_url);
    }

    return $url;
  }
}
