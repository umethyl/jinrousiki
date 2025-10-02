<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('game_config', 'room_config', 'time_config', 'message', 'feedengine',
		 'image_class');

DB::Connect(); // DB 接続
$site_summary = FeedEngine::Initialize('site_summary.php');
echo $site_summary->Export();
