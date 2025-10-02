<?php
require_once('include/init.php');
Loader::LoadFile('icon_view_class');
Loader::LoadRequest('RequestIconView');
IconView::Output();
