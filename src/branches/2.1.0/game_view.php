<?php
require_once('include/init.php');
Loader::LoadFile('game_view_class');
Loader::LoadRequest('RequestBaseGame', true);
GameView::Output();
