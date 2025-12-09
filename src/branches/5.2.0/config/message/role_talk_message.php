<?php
//-- 役職会話メッセージ --//
class RoleTalkMessage {
  /* 遠吠え・囁き (ユーザ名欄) */
  const WOLF   = '狼の遠吠え';
  const COMMON = '共有者の小声';
  const LOVERS = '恋人の囁き';

  /* 遠吠え・囁き (発言) */
  const WOLF_HOWL   = 'アオォーン・・・'; //人狼の遠吠え
  const COMMON_TALK = 'ヒソヒソ・・・'; //共有者の囁き
  const LOVERS_TALK = 'うふふ・・・うふふ・・・'; //恋人の囁き
  const HOWLING     = 'キィーーン・・・'; //スピーカーの音割れ効果音

  /* 発言置換能力者 */
  const CUTE_WOLF = ''; //不審者・萌系 (空なら人狼の遠吠えになる)
  public static $gentleman = "お待ち下さい。\n%sさん、ハンケチーフを落としておりますぞ。"; //紳士
  public static $lady      = "お待ちなさい！\n%s、タイが曲がっていてよ。"; //淑女

  /* 妖精 */
  const SPRING_FAIRY = '春ですよー';
  const SUMMER_FAIRY = '夏ですよー';
  const AUTUMN_FAIRY = '秋ですよー';
  const WINTER_FAIRY = '冬ですよー';
}
