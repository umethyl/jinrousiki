<?php
require_once('init.php');
Loader::LoadFile('old_log_class');
Loader::LoadRequest('RequestOldLog');
OldLog::Output();
