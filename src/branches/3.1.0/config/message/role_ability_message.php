<?php
//-- 役職能力メッセージ --//
class RoleAbilityMessage {
  /* 投票メッセージ */
  const VOTE_KILL     = '処刑する人を選択してください';		//処刑
  const MAGE          = '占う人を選択してください';		//占い師
  const VOODOO_KILLER = '呪いを祓う人を選択してください';	//陰陽師
  const GUARD         = '護衛する人を選択してください';		//狩人
  const REPORTER      = '尾行する人を選択してください';		//ブン屋
  const ANTI_VOODOO   = '厄を祓う人を選択してください';		//厄神
  const REVIVE        = '蘇生する人を選択してください';		//猫又
  const ASSASSIN      = '暗殺する人を選択してください';		//暗殺者
  const SCAN          = '心を読む人を選択してください';		//さとり
  const WIZARD        = '魔法をかける人を選択してください';	//魔法使い
  const ESCAPER       = '逃亡する先を選択してください';		//逃亡者
  const WOLF          = '喰い殺す人を選択してください';		//人狼
  const JAMMER        = '占いを妨害する人を選択してください';	//月兎
  const VOODOO        = '呪いをかける人を選択してください';	//呪術師
  const STEP          = '徘徊する人を選択してください';		//家鳴
  const DREAM_EATER   = '夢を食べる人を選択してください';	//獏
  const POSSESSED     = '憑依する人を選択してください';		//犬神
  const GRAVE         = '墓を荒らす人を選択してください';	//墓荒らし
  const TRAP          = '罠を設置する先を選択してください';	//罠師
  const SPY           = '離脱を行うか選択してください';		//内通者
  const CUPID         = '結びつける人を選択してください';	//キューピッド
  const VAMPIRE       = '吸血する人を選択してください';		//吸血鬼
  const FAIRY         = '悪戯する人を選択してください';		//妖精
  const OGRE          = '攫う人を選択してください';		//鬼
  const DUELIST       = '結びつける人を選択してください';	//決闘者
  const TENGU         = '神通力をかける人を選択してください';	//天狗
  const MANIA         = '能力を真似る人を選択してください';	//神話マニア
  const DEATH_NOTE    = '名前を書く人を選択してください';	//デスノート

  /* 死亡 */
  const DEAD = 'アナタは息絶えました・・・';

  /* 投票済み情報 */
  const VOTED        = '投票済み';
  const NOT_VOTED    = 'まだ投票していません';
  const SETTLE_VOTED = 'さんに投票済み';
  const CANCEL_VOTED = 'キャンセル投票済み';
  const HONORIFIC    = 'さん';

  /* 処刑投票情報 */
  const VOTE_COUNT   = '投票 %d 回目：';
}
