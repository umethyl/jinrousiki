<?php
//-- 投票ログメッセージ (Talk 用) --//
class VoteTalkMessage {
  const FORMAT = ' は %s'; //表示フォーマット

  static public $OBJECTION         = ' が「異議」を申し立てました';	//「異議」あり
  static public $KICK_DO           = ' に KICK 投票しました';		//KICK
  static public $VOTE_DO           = ' に処刑投票しました';		//処刑
  static public $WOLF_EAT          = ' に狙いをつけました';		//人狼
  static public $SILENT_WOLF_EAT   = ' に静かに狙いをつけました';	//響狼 (ステルス)
  static public $ESCAPE_DO         = ' の周辺に逃亡しました';		//逃亡者
  static public $MAGE_DO           = ' を占います';			//占い師
  static public $VOODOO_KILLER_DO  = ' の呪いを祓います';		//陰陽師
  static public $JAMMER_DO         = ' の占いを妨害します';		//月兎
  static public $TRAP_DO           = ' の周辺に罠を仕掛けました';	//罠師
  static public $TRAP_NOT_DO       = ' は罠設置を行いませんでした';	//罠師 (キャンセル)
  static public $POSSESSED_DO      = ' に憑依します';			//犬神
  static public $POSSESSED_NOT_DO  = ' は憑依を行いませんでした';	//犬神 (キャンセル)
  static public $VOODOO_DO         = ' に呪いをかけます';		//呪術師
  static public $DREAM_EAT         = ' に狙いをつけました';		//獏
  static public $STEP_DO           = ' の周辺を徘徊します';		//家鳴
  static public $STEP_NOT_DO       = ' は徘徊を行いませんでした';	//家鳴 (キャンセル)
  static public $GUARD_DO          = ' の護衛に付きました';		//狩人
  static public $ANTI_VOODOO_DO    = ' の厄を祓います';			//厄神
  static public $REPORTER_DO       = ' を尾行しました';			//ブン屋
  static public $REVIVE_DO         = ' に蘇生処置をしました';		//猫又
  static public $REVIVE_NOT_DO     = ' は蘇生処置をしませんでした';	//猫又 (キャンセル)
  static public $ASSASSIN_DO       = ' に狙いをつけました';		//暗殺者
  static public $ASSASSIN_NOT_DO   = ' は暗殺を行いませんでした';	//暗殺者 (キャンセル)
  static public $MIND_SCANNER_DO   = ' の心を読みます';			//さとり
  static public $WIZARD_DO         = ' に魔法をかけました';		//魔法使い
  static public $CUPID_DO          = ' に愛の矢を放ちました';		//キューピッド
  static public $VAMPIRE_DO        = ' に狙いをつけました';		//吸血鬼
  static public $FAIRY_DO          = ' に悪戯しました';			//妖精
  static public $OGRE_DO           = ' に狙いをつけました';		//鬼
  static public $OGRE_NOT_DO       = ' は人攫いを行いませんでした';	//鬼 (キャンセル)
  static public $DUELIST_DO        = ' に宿命を結び付けました';		//決闘者
  static public $MANIA_DO          = ' の能力を真似ることにしました';	//神話マニア
  static public $DEATH_NOTE_DO     = ' の名前を書きました';		//デスノート
  static public $DEATH_NOTE_NOT_DO = ' はデスノートを使いませんでした';	//デスノート (キャンセル)
}
