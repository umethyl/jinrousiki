<?php
require_once('init.php');
Loader::LoadFile('room_manager_class');

if (! DB::ConnectInHeader()) return false;
if (Loader::IsLoaded('index_functions')) RoomManager::Maintenance();

Loader::LoadRequest('RequestRoomManager');
RoomManager::Execute();
DB::Disconnect();
