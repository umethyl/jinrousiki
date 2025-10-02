<?php
//-- 定数リスト (Room/Scene) --//
final class RoomScene {
  const BEFORE      = 'beforegame';
  const DAY         = 'day';
  const NIGHT       = 'night';
  const AFTER       = 'aftergame';
  const HEAVEN      = 'heaven';
  const HEAVEN_ONLY = 'heaven_only';
}

//-- 定数リスト (Room/Status) --//
final class RoomStatus {
  const WAITING  = 'waiting';
  const CLOSING  = 'closing';
  const PLAYING  = 'playing';
  const FINISHED = 'finished';
}

//-- 定数リスト (Room/Mode) --//
final class RoomMode {
  const VIEW      = 'view';
  const DEAD      = 'dead';
  const HEAVEN    = 'heaven';
  const LOG       = 'log';
  const WATCH     = 'watch';
  const SINGLE    = 'single';
  const PERSONAL  = 'personal';
  const AUTO_PLAY = 'auto_play';
  const TEST      = 'test';
}
