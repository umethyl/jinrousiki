<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('test_class', 'chaos_config', 'cast_class');
ChaosVersoTest::Output();
