<?php
require_once('include/init.php');
Loader::LoadFile('game_play_class');
Loader::LoadRequest('RequestGamePlay', true);
GamePlay::Output();
