<?php
require_once('init.php');

$disable = true; //false にすると使用可能になる
if ($disable) HTML::OutputUnusableError();

Loader::LoadFile('test_class', 'twitter_class');
TwitterTest::Output();
