<?php
//-- 役職投票画面メッセージ --//
class VoteRoleMessage {
  /* 投票ボタン */
  public static $MAGE_DO           = '対象を占う';			//占い師
  public static $VOODOO_KILLER_DO  = '対象の呪いを祓う';		//陰陽師
  public static $GUARD_DO          = '対象を護衛する';			//狩人
  public static $ANTI_VOODOO_DO    = '対象の厄を祓う';			//厄神
  public static $REPORTER_DO       = '対象を尾行する';			//ブン屋
  public static $POISON_CAT_DO     = '対象を蘇生する';			//猫又
  public static $POISON_CAT_NOT_DO = '誰も蘇生しない';			//猫又 (キャンセル)
  public static $ASSASSIN_DO       = '対象を暗殺する';			//暗殺者
  public static $ASSASSIN_NOT_DO   = '誰も暗殺しない';			//暗殺者 (キャンセル)
  public static $MIND_SCANNER_DO   = '対象の心を読む';			//さとり
  public static $STEP_SCANNER_DO   = '対象の周辺の心を読む';		//雷神
  public static $WIZARD_DO         = '対象に魔法をかける';		//魔法使い
  public static $ESCAPE_DO         = '対象の周辺に逃亡する';		//逃亡者
  public static $WOLF_EAT          = '対象を喰い殺す (先着)';		//人狼
  public static $SILENT_WOLF_EAT   = '音を立てない (回数限定)';		//響狼 (ステルス)
  public static $VOODOO_MAD_DO     = '対象に呪いをかける';		//呪術師
  public static $JAMMER_MAD_DO     = '対象の占いを妨害する';		//月兎
  public static $DREAM_EAT         = '対象の夢を喰う';			//獏
  public static $TRAP_MAD_DO       = '対象の周辺に罠を設置する';	//罠師
  public static $TRAP_MAD_NOT_DO   = '罠を設置しない';			//罠師 (キャンセル)
  public static $POSSESSED_DO      = '対象に憑依する';			//犬神
  public static $POSSESSED_NOT_DO  = '誰にも憑依しない';		//犬神 (キャンセル)
  public static $GRAVE_DO          = '対象の墓を荒らす';		//墓荒らし
  public static $GRAVE_NOT_DO      = '墓を荒らさない';			//墓荒らし (キャンセル)
  public static $STEP_DO           = '対象の周辺を徘徊する';		//家鳴
  public static $STEP_NOT_DO       = 'どこにも徘徊しない';		//家鳴 (キャンセル)
  public static $EXIT_DO           = '離脱する';			//内通者
  public static $EXIT_NOT_DO       = '離脱しない';			//内通者 (キャンセル)
  public static $CUPID_DO          = '対象に愛の矢を放つ';		//キューピッド
  public static $VAMPIRE_DO        = '対象を吸血する';			//吸血鬼
  public static $FAIRY_DO          = '対象に悪戯する';			//妖精
  public static $OGRE_DO           = '対象を攫う';			//鬼
  public static $OGRE_NOT_DO       = '誰も攫わない';			//鬼 (キャンセル)
  public static $DUELIST_DO        = '対象を結びつける';		//決闘者
  public static $TENGU_DO          = '対象に神通力をかける';		//天狗
  public static $MANIA_DO          = '対象を真似る';			//神話マニア
  public static $DEATH_NOTE_DO     = '対象の名前を書く';		//デスノート
  public static $DEATH_NOTE_NOT_DO = '誰の名前も書かない';		//デスノート (キャンセル)

  /* エラー表示 */
  //投票不可
  const NO_ACTION               = '夜：あなたは投票できません';
  const IMPOSSIBLE_FIRST_DAY    = '夜：初日は投票できません';
  const POSSIBLE_ONLY_FIRST_DAY = '夜：初日以外は投票できません';
  const IMPOSSIBLE_VOTE_DAY     = '夜：投票できる日ではありません';
  const LOST_ABILITY            = '夜：能力喪失しています';
  const OPEN_CAST               = '夜：「霊界で配役を公開しない」オプションがオフの時は投票できません';
  const INVALID_TARGET          = '夜：投票先が正しくありません';

  //無効投票
  const TARGET_MYSELF         = '自分には投票できません';
  const TARGET_DEAD           = '死者には投票できません';
  const TARGET_ALIVE          = '死者以外には投票できません';
  const TARGET_DUMMY_BOY      = '身代わり君には投票できません';
  const TARGET_ONLY_DUMMY_BOY = '身代わり君使用の場合は、身代わり君以外に投票できません';
  const TARGET_QUIZ           = 'クイズ村ではGM以外に投票できません';
  const TARGET_WOLF           = '狼同士には投票できません';
  const TARGET_INCLUDE_MYSELF = '必ず自分を対象に含めてください';
  const TARGET_MYSELF_COUNT   = '少人数村の場合は、必ず自分を対象に含めてください';
  const INVALID_TARGET_COUNT  = '指定人数は %d 人にしてください';
  const INVALID_TARGET_RANGE  = '指定人数は1～4人にしてください';
  const INVALID_VECTOR        = '方向転換は一回まで';
  const INVALID_ROUTE         = '通り道は直線にしてください';
  const UNCHAINED_ROUTE       = '通り道が一本に繋がっていません';
  const UNCHAINED_SELF        = '通り道が自分と繋がっていません';
}
