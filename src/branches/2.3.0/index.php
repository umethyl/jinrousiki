<?php
require_once('init.php');
Loader::LoadFile('index_functions');
Loader::LoadRequest('RequestIndex');
IndexHTML::Execute();
