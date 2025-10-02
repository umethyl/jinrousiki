<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('CAST_CONF', 'ROLE_DATA');
OutputInfoPageHeader('配役一覧', 0, 'info_cast');
$CAST_CONF->OutputCastTable();
OutputHTMLFooter();
