<?php
require_once('init.php');
Loader::LoadFile('game_up_class');
Loader::LoadRequest('RequestGameUp', true);
GameUp::Output();
