<?php
require_once('init.php');

$disable = true; //false にすると使用可能になる
if (true === $disable) {
  HTML::OutputUnusableError();
}

Loader::LoadFile('twitter_test_class');
TwitterTestController::Execute();
