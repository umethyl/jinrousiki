<?php
require_once('init.php');

$disable = true; //false にすると使用可能になる
if ($disable) HTML::OutputUnusableError();

Loader::LoadFile('game_config', 'room_config', 'time_config', 'feedengine', 'image_class');

DB::Connect();
$site_summary = FeedEngine::Initialize('site_summary.php');
echo $site_summary->Export();
