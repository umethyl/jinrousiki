<?php
require_once('init.php');

if (true !== GameConfig::ASYNC) {
  $title = Message::SESSION_ERROR;
  HTML::OutputResult($title, $title . Message::TOP);
}

Loader::LoadFile('game_play_class');
RQ::LoadRequest('game_play');
GamePlayController::ExecuteAsync();
