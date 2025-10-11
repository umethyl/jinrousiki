<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once('init.php');
Loader::LoadFile('step_vote_test_class');
StepVoteTestController::Execute();
