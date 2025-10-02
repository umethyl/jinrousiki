<?php
require_once('init.php');
Loader::LoadFile('game_play_class');
Loader::LoadRequest('RequestGamePlay', true);

if (! GameConfig::ASYNC) {
  $title = Message::SESSION_ERROR;
  HTML::OutputResult($title, $title . Message::TOP);
}
$builder = new GamePlay();
$builder->Load();
$builder->view->SetSound();
$builder->CheckSilence();
$builder->view->SetURL();
$builder->view->OutputTalkAsync();
