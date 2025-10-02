<?php
require_once('include/init.php');
Loader::LoadFile('icon_edit_class');
Loader::LoadRequest('RequestIconEdit');
IconEdit::Execute();
