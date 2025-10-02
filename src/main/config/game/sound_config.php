<?php
//-- 音源設定 --//
class SoundConfig {
  const PATH      = 'swf'; //音源のパス
  const EXTENSION = 'swf'; //拡張子

  static $entry            = 'sound_entry';		//入村
  static $full             = 'sound_full';		//定員
  static $morning          = 'sound_morning';		//夜明け
  static $revote           = 'sound_revote';		//再投票
  static $novote           = 'sound_novote';		//未投票告知
  static $alert            = 'sound_alert';		//未投票警告
  static $objection_male   = 'sound_objection_male';	//異議あり(男)
  static $objection_female = 'sound_objection_female';	//異議あり(女)
}
