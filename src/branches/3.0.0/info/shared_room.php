<?php
require_once('init.php');
Loader::LoadFile('shared_server_config');
Loader::LoadRequest('RequestSharedRoom');

//-- ◆ 文字化け抑制 --//
if (0 < RQ::Get()->id && RQ::Get()->id <= count(SharedServerConfig::$server_list)) {
  InfoHTML::OutputSharedRoom(RQ::Get()->id);
}
else {
  InfoHTML::OutputHeader('関連サーバ村情報', 0, 'shared_room');
  InfoHTML::OutputSharedRoomList();
  HTML::OutputFooter();
}
