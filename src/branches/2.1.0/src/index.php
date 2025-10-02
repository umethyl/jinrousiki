<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('src_class');
SrcHTML::Output();
