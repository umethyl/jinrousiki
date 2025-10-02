<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('SCRIPT_INFO');
OutputInfoPageHeader('謝辞・素材', 0, 'info_cast');
$config = new CopyrightConfig();
$config->Output();
$SCRIPT_INFO->OutputSystem();
OutputHTMLFooter();
