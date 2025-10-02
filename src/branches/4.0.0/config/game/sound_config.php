<?php
//-- 音源設定 --//
class SoundConfig {
  const PATH      = 'sound'; //音源のパス
  const EXTENSION = 'mp3'; //拡張子

  public static $entry			= 'sound_entry';		//入村
  public static $full			= 'sound_full';			//定員
  public static $morning		= 'sound_morning';		//夜明け
  public static $night			= 'sound_night';		//日没 (遠吠え)
  public static $vote_success		= 'sound_vote_success';		//投票完了
  public static $revote			= 'sound_revote';		//再投票
  public static $novote			= 'sound_novote';		//未投票告知
  public static $alert			= 'sound_alert';		//未投票警告
  public static $objection_male		= 'sound_objection_male';	//異議あり(男)
  public static $objection_female	= 'sound_objection_female';	//異議あり(女)
}
