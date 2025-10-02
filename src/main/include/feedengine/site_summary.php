<?php
class SiteSummary extends FeedEngine {
  public $room_info = array();

  function Build() {
    $this->SetChannel(ServerConfig::TITLE, ServerConfig::SITE_ROOT, ServerConfig::COMMENT);
    foreach (RoomDataDB::LoadOpening() as $ROOM) {
      $title = "{$ROOM->name}村";
      $url = "{$this->uri}game_view.php?room_no={$ROOM->id}";
      $list = array(
        'game_option' => $ROOM->game_option->row,
	'option_role' => $ROOM->option_role->row,
	'max_user'    => $ROOM->max_user);
      RoomOption::Load($list);
      $options = RoomOption::GenerateImage();
      $status  = Image::Room()->Generate($ROOM->status);
      $description = <<<XHTML
<div>
<a href="{$url}">
{$status}<span class='room_no'>[{$ROOM->id}番地]</span><h2>{$title}</h2>
～ {$ROOM->comment} ～ {$options}(最大{$ROOM->max_user}人)
</a>
</div>

XHTML;
      $description = strtr($description, array('./' => $this->url));
      $description = preg_replace('#(<img .*?[^/])>#i', '$1/>', $description);
      $this->AddItem($title, $url, $description);
    }
  }
}
