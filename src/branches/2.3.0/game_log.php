<?php
require_once('init.php');
Loader::LoadFile('game_log_class');
Loader::LoadRequest('RequestGameLog');
GameLog::Execute();
