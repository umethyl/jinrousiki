<?php
require_once('init.php');
Loader::LoadFile('game_play_class');
Loader::LoadRequest('RequestGamePlay', true);
GamePlay::Execute();
