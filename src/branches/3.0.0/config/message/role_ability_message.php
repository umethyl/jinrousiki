<?php
//-- 役職能力メッセージ --//
class RoleAbilityMessage {
  /* 投票メッセージ */
  static public $dead             = 'アナタは息絶えました・・・';		//死者
  static public $vote_kill        = '処刑する人を選択してください';		//処刑
  static public $wolf_eat         = '喰い殺す人を選択してください';		//人狼
  static public $mage_do          = '占う人を選択してください';			//占い師
  static public $voodoo_killer_do = '呪いを祓う人を選択してください';		//陰陽師
  static public $guard_do         = '護衛する人を選択してください';		//狩人
  static public $reporter_do      = '尾行する人を選択してください';		//ブン屋
  static public $anti_voodoo_do   = '厄を祓う人を選択してください';		//厄神
  static public $revive_do        = '蘇生する人を選択してください';		//猫又
  static public $assassin_do      = '暗殺する人を選択してください';		//暗殺者
  static public $mind_scanner_do  = '心を読む人を選択してください';		//さとり
  static public $wizard_do        = '魔法をかける人を選択してください';		//魔法使い
  static public $escape_do        = '逃亡する先を選択してください';		//逃亡者
  static public $jammer_do        = '占いを妨害する人を選択してください';	//月兎
  static public $voodoo_do        = '呪いをかける人を選択してください';		//呪術師
  static public $step_do          = '徘徊する人を選択してください';		//家鳴
  static public $dream_eat        = '夢を食べる人を選択してください';		//獏
  static public $possessed_do     = '憑依する人を選択してください';		//犬神
  static public $trap_do          = '罠を設置する先を選択してください';		//罠師
  static public $cupid_do         = '結びつける人を選択してください';		//キューピッド
  static public $vampire_do       = '吸血する人を選択してください';		//吸血鬼
  static public $fairy_do         = '悪戯する人を選択してください';		//妖精
  static public $ogre_do          = '攫う人を選択してください';			//鬼
  static public $duelist_do       = '結びつける人を選択してください';		//決闘者
  static public $tengu_do         = '神通力をかける人を選択してください';	//天狗
  static public $mania_do         = '能力を真似る人を選択してください';		//神話マニア
  static public $death_note_do    = '名前を書く人を選択してください';		//デスノート

  /* 投票済み情報 */
  const VOTED        = '投票済み';
  const NOT_VOTED    = 'まだ投票していません';
  const SETTLE_VOTED = 'さんに投票済み';
  const CANCEL_VOTED = 'キャンセル投票済み';
  const HONORIFIC    = 'さん';

  /* 処刑投票情報 */
  const VOTE_COUNT   = '投票 %d 回目：';
}
