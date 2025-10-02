<?php
require_once('init.php');
Loader::LoadFile('user_manager_class');
Loader::LoadRequest('RequestUserManager');
UserManager::Execute();
