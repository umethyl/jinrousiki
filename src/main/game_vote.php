<?php
require_once('init.php');
Loader::LoadFile('game_vote_class');
Loader::LoadRequest('RequestGameVote', true);
GameVote::Execute();
