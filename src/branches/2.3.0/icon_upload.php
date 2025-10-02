<?php
require_once('init.php');
Loader::LoadFile('icon_upload_class');
Loader::LoadRequest('RequestIconUpload');
IconUpload::Execute();
