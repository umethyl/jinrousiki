<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('cast_config', 'role_data_class', 'info_functions');
InfoHTML::OutputHeader('配役一覧', 0, 'cast');
InfoHTML::OutputCast();
HTML::OutputFooter();
