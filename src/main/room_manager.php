<?php
require_once('include/init.php');
Loader::LoadFile('room_manager_class');

if (! DB::ConnectInHeader()) return false;
if (Loader::IsLoaded('index_functions')) RoomManager::Maintenance();

Loader::LoadRequest('RequestRoomManager');
if (RQ::$get->create_room) {
  Loader::LoadFile('message', 'user_icon_class', 'twitter_class');
  RoomManager::Create();
}
elseif (RQ::$get->change_room) {
  Loader::LoadFile('session_class', 'user_class');
  RoomManager::Create();
}
elseif (RQ::$get->room_no > 0) {
  Loader::LoadFile('session_class', 'user_class', 'option_form_class');
  RoomManager::OutputCreate();
}
else {
  Loader::LoadFile('chaos_config');
  RoomManager::OutputList();
}
DB::Disconnect();
