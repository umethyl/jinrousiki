<?php
require_once('init.php');
Loader::LoadFile('game_play_class');
Loader::LoadRequest('game_play', true);

if (! GameConfig::ASYNC) {
  $title = Message::SESSION_ERROR;
  HTML::OutputResult($title, $title . Message::TOP);
}
GamePlay::Load();
GamePlay::CheckSilence();
GamePlay::OutputTalkAsync();
