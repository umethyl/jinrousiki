<?php
require_once('init.php');
Loader::LoadFile('copyright_config');
InfoHTML::OutputHeader('謝辞・素材', 0, 'copyright');
InfoHTML::OutputCopyright();
HTML::OutputFooter();
