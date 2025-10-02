<?php
//-- 投票画面専用メッセージ --//
class VoteMessage {
  //beforegame
  static public $KICK_DO    = '対象をキックするに一票';	//KICK 投票ボタン
  static public $GAME_START = 'ゲームを開始するに一票';	//ゲーム開始ボタン

  //day
  static public $VOTE_DO = '対象を処刑するに一票';	//処刑投票ボタン

  //night
  static public $CAUTION           = '* 投票先の変更はできません。慎重に！';	//注意事項
  static public $WOLF_EAT          = '対象を喰い殺す (先着)';	//人狼
  static public $SILENT_WOLF_EAT   = '音を立てない (回数限定)';	//響狼(ステルス)
  static public $MAGE_DO           = '対象を占う';		//占い師
  static public $VOODOO_KILLER_DO  = '対象の呪いを祓う';	//陰陽師
  static public $GUARD_DO          = '対象を護衛する';		//狩人
  static public $ANTI_VOODOO_DO    = '対象の厄を祓う';		//厄神
  static public $REPORTER_DO       = '対象を尾行する';		//ブン屋
  static public $REVIVE_DO         = '対象を蘇生する';		//猫又
  static public $REVIVE_NOT_DO     = '誰も蘇生しない';		//猫又(キャンセル)
  static public $ASSASSIN_DO       = '対象を暗殺する';		//暗殺者
  static public $ASSASSIN_NOT_DO   = '誰も暗殺しない';		//暗殺者(キャンセル)
  static public $MIND_SCANNER_DO   = '対象の心を読む';		//さとり
  static public $WIZARD_DO         = '対象に魔法をかける';	//魔法使い
  static public $ESCAPE_DO         = '対象の周辺に逃亡する';	//逃亡者
  static public $VOODOO_DO         = '対象に呪いをかける';	//呪術師
  static public $JAMMER_DO         = '対象の占いを妨害する';	//月兎
  static public $DREAM_EAT         = '対象の夢を喰う';		//獏
  static public $TRAP_DO           = '対象の周辺に罠を設置する';//罠師
  static public $TRAP_NOT_DO       = '罠を設置しない';		//罠師(キャンセル)
  static public $POSSESSED_DO      = '対象に憑依する';		//犬神
  static public $POSSESSED_NOT_DO  = '誰にも憑依しない';	//犬神(キャンセル)
  static public $STEP_DO           = '対象の周辺を徘徊する';	//家鳴
  static public $STEP_NOT_DO       = 'どこにも徘徊しない';	//家鳴(キャンセル)
  static public $CUPID_DO          = '対象に愛の矢を放つ';	//キューピッド
  static public $VAMPIRE_DO        = '対象を吸血する';		//吸血鬼
  static public $FAIRY_DO          = '対象に悪戯する';		//妖精
  static public $OGRE_DO           = '対象を攫う';		//鬼
  static public $OGRE_NOT_DO       = '誰も攫わない';		//鬼(キャンセル)
  static public $DUELIST_DO        = '対象を結びつける';	//決闘者
  static public $MANIA_DO          = '対象を真似る';		//神話マニア
  static public $DEATH_NOTE_DO     = '対象の名前を書く';	//デスノート
  static public $DEATH_NOTE_NOT_DO = '誰の名前も書かない';	//デスノート(キャンセル)
  static public $REVIVE_REFUSE     = '蘇生を辞退する';		//蘇生辞退
  static public $RESET_TIME        = '超過時間リセット';	//超過時間リセット(管理者用)
}
