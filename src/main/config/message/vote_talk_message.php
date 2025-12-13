<?php
//-- 投票ログメッセージ (Talk 用) --//
class VoteTalkMessage {
  const FORMAT = ' は %s'; //表示フォーマット

  public static $OBJECTION          = ' が「異議」を申し立てました';		//「異議」あり (後方互換用)
  public static $OBJECTION_MALE     = ' (♂) が「異議」を申し立てました';	//「異議」あり/男
  public static $OBJECTION_FEMALE   = ' (♀) が「異議」を申し立てました';	//「異議」あり/女
  public static $KICK_DO            = ' に KICK 投票しました';			//KICK
  public static $VOTE_DO            = ' に処刑投票しました';			//処刑
  public static $MAGE_DO            = ' を占います';				//占い師
  public static $VOODOO_KILLER_DO   = ' の呪いを祓います';			//陰陽師
  public static $GUARD_DO           = ' の護衛に付きました';			//狩人
  public static $REPORTER_DO        = ' を尾行します';				//ブン屋
  public static $ANTI_VOODOO_DO     = ' の厄を祓います';			//厄神
  public static $REVIVE_DO          = ' に蘇生処置をします';			//猫又
  public static $REVIVE_NOT_DO      = ' は蘇生処置をしませんでした';		//猫又 (キャンセル)
  public static $ASSASSIN_DO        = ' に狙いをつけました';			//暗殺者
  public static $ASSASSIN_NOT_DO    = ' は暗殺を行いませんでした';		//暗殺者 (キャンセル)
  public static $MIND_SCANNER_DO    = ' の心を読みます';			//さとり
  public static $WIZARD_DO          = ' に魔法をかけます';			//魔法使い
  public static $SERVANT_DO         = ' を主に選択します';			//従者 (主選択)
  public static $SERVANT_END_DO     = ' を裏切ります';				//従者 (裏切り)
  public static $SERVANT_END_NOT_DO = ' は主を裏切りませんでした';		//従者 (裏切り/キャンセル)
  public static $ESCAPE_DO          = ' の周辺に逃亡しました';			//逃亡者
  public static $WOLF_EAT           = ' に狙いをつけました';			//人狼
  public static $SILENT_WOLF_EAT    = ' に静かに狙いをつけました';		//響狼 (ステルス)
  public static $JAMMER_DO          = ' の占いを妨害します';			//月兎
  public static $VOODOO_DO          = ' に呪いをかけます';			//呪術師
  public static $STEP_DO            = ' の周辺を徘徊します';			//家鳴
  public static $STEP_NOT_DO        = ' は徘徊を行いませんでした';		//家鳴 (キャンセル)
  public static $DREAM_EAT          = ' に狙いをつけました';			//獏
  public static $POSSESSED_DO       = ' に憑依します';				//犬神
  public static $POSSESSED_NOT_DO   = ' は憑依を行いませんでした';		//犬神 (キャンセル)
  public static $GRAVE_DO           = ' の墓を荒らします';			//墓荒らし
  public static $GRAVE_NOT_DO       = ' は墓を荒らしませんでした';		//墓荒らし (キャンセル)
  public static $RIOTE_DO           = ' の周辺に張り込みます';			//暴徒
  public static $RIOTE_NOT_DO       = ' は張り込みしませんでした';		//暴徒 (キャンセル)
  public static $TRAP_DO            = ' の周辺に罠を仕掛けます';		//罠師
  public static $TRAP_NOT_DO        = ' は罠を仕掛けませんでした';		//罠師 (キャンセル)
  public static $EXIT_DO            = ' は離脱を決行しました';			//内通者
  public static $EXIT_NOT_DO        = ' は離脱しませんでした';			//内通者 (キャンセル)
  public static $CUPID_DO           = ' に愛の矢を放ちました';			//キューピッド
  public static $VAMPIRE_DO         = ' に狙いをつけました';			//吸血鬼
  public static $FAIRY_DO           = ' に悪戯します';				//妖精
  public static $OGRE_DO            = ' に狙いをつけました';			//鬼
  public static $OGRE_NOT_DO        = ' は人攫いを行いませんでした';		//鬼 (キャンセル)
  public static $DUELIST_DO         = ' に宿命を結び付けました';		//決闘者
  public static $TENGU_DO           = ' に神通力を使います';			//天狗
  public static $MANIA_DO           = ' の能力を真似ます';			//神話マニア
  public static $DEATH_NOTE_DO      = ' の名前を書きます';			//デスノート
  public static $DEATH_NOTE_NOT_DO  = ' は名前を書きませんでした';		//デスノート (キャンセル)
}
