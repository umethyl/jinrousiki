<?php
require_once('include/init.php');
Loader::LoadFile('game_frame_class');
Loader::LoadRequest('RequestGameFrame', true);
GameFrame::Output();
