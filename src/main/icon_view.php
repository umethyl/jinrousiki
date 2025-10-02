<?php
require_once('include/init.php');
$INIT_CONF->LoadFile('icon_functions');
$INIT_CONF->LoadClass('SESSION');
$INIT_CONF->LoadRequest('RequestIconView'); //引数を取得
$DB_CONF->Connect(); //DB 接続
OutputIconPageHeader();
OutputIconList();
OutputHTMLFooter();
