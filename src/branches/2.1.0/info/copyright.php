<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('copyright_config');
InfoHTML::OutputHeader('謝辞・素材', 0, 'info');
InfoHTML::OutputCopyright();
HTML::OutputFooter();
