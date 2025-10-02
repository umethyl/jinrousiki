<?php
//-- 基本会話メッセージ --//
//-- ◆文字化け抑制◆ --//
class TalkMessage {
  /* ユーザ登録 */
  //const ENTRY_USER = '%s が村の集会場にやってきました'; //入村メッセージ
  const ENTRY_USER = '%s が幻想入りしました';

  /* 投票 */
  const KICK_OUT = 'は席をあけわたし、村から去りました'; //Kick 処理

  /* シーン切り替え */
  const MORNING    = '朝日が昇り、%s 日目の朝がやってきました';	//朝
  const NIGHT      = '日が落ち、暗く静かな夜がやってきました';	//夜
  const CHAOS      = '配役隠蔽モードです';			//配役隠蔽通知 (闇鍋用)
  const SKIP_NIGHT = '白澤の能力で夜が飛ばされました……';	//白澤の能力発動

  /* 探偵告知 */
  const DETECTIVE = '探偵は %s さんです';

  /* 表示カスタム */
  const QUOTE         = '「%s」';	//GameConfig::QUOTE_TALK
  const SECRET_SYMBOL = '[密]';		//秘密発言

  /* タイムスタンプ */
  const ESTABLISH  = '村作成：';
  const GAME_START = 'ゲーム開始：';
  const GAME_END   = 'ゲーム終了：';

  /* 夜の発言 */
  const COMMON    = '共有者';
  const WOLF      = '人狼';
  const MAD       = '囁き狂人';
  const FOX       = '妖狐';
  const SELF_TALK = 'の独り言';
}
