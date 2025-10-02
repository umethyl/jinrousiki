<?php
require_once('include/init.php');
if (Security::CheckValue($_FILES)) die;
Loader::LoadFile('icon_upload_class');
IconUpload::Execute();
