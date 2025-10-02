<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('shared_server_config');
Loader::LoadRequest('RequestSharedRoom');

if (0 < RQ::Get()->id && RQ::Get()->id <= count(SharedServerConfig::$server_list)) {
  InfoHTML::OutputSharedRoom(RQ::Get()->id);
}
else {
  InfoHTML::OutputHeader('関連サーバ村情報', 0, 'shared_room');
  InfoHTML::OutputSharedRoomList();
  HTML::OutputFooter();
}
