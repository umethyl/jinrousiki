<?php
require_once('include/init.php');
Loader::LoadFile('game_up_class');
Loader::LoadRequest('RequestGameUp', true);
GameUp::Output();
