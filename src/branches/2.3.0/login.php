<?php
require_once('init.php');
Loader::LoadFile('login_class');
Loader::LoadRequest('RequestLogin', true);
Login::Execute();
