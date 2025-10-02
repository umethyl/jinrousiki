<?php
require_once('init.php');
Loader::LoadFile('cast_config', 'role_data_class', 'info_functions');
InfoHTML::OutputHeader('配役一覧', 0, 'cast');
InfoHTML::OutputCast();
HTML::OutputFooter();
