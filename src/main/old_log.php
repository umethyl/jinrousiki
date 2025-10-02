<?php
require_once('include/init.php');
Loader::LoadFile('old_log_class');
Loader::LoadRequest('RequestOldLog');
OldLog::Output();
