<?php
require_once('include/init.php');
Loader::LoadFile('game_vote_class');
Loader::LoadRequest('RequestGameVote', true);
GameVote::Output();
