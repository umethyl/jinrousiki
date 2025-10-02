<?php
require_once('init.php');
Loader::LoadFile('game_view_class');
Loader::LoadRequest('RequestBaseGame', true);
GameView::Execute();
