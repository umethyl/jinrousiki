<?php
require_once('include/init.php');
Loader::LoadFile('login_class');
Loader::LoadRequest('RequestLogin', true);
Login::Execute();
