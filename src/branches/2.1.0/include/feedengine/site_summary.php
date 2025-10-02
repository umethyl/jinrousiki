<?php
class SiteSummary extends FeedEngine {
  public $room_info = array();

  function Build() {
    $this->SetChannel(ServerConfig::TITLE, ServerConfig::SITE_ROOT, ServerConfig::COMMENT);
    $rooms = RoomDataSet::LoadOpeningRooms();
    foreach ($rooms->rows as $room) {
      $title = "{$room->name}村";
      $url = "{$this->uri}game_view.php?room_no={$room->id}";
      $options = RoomOption::GenerateImage($room->game_option->row, $room->option_role->row);
      $status  = Image::Room()->Generate($room->status);
      $description = <<<XHTML
<div>
<a href="{$url}">
{$status}<span class='room_no'>[{$room->id}番地]</span><h2>{$title}</h2>
～ {$room->comment} ～ {$options}(最大{$room->max_user}人)
</a>
</div>

XHTML;
      $description = strtr($description, array('./' => $this->url));
      $description = preg_replace('#(<img .*?[^/])>#i', '$1/>', $description);
      $this->AddItem($title, $url, $description);
    }
  }
}
