<?php
require_once('include/init.php');
Loader::LoadFile('game_log_class');
Loader::LoadRequest('RequestGameLog');
GameLog::Output();
