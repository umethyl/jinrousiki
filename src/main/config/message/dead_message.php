<?php
//-- 死亡メッセージ --//
class DeadMessage {
  /* 基本 */
  static public $vote_killed        = 'は投票の結果処刑されました';			//処刑
  static public $vote_cancelled     = 'の処刑は無かったことにされました';		//処刑キャンセル
  static public $blind_vote         = '傘化けの能力で投票結果が隠されました';		//傘化け
  static public $deadman            = 'は無残な姿で発見されました';			//共通死亡メッセージ
  static public $wolf_killed        = 'は人狼の餌食になったようです';			//人狼襲撃
  static public $hungry_wolf_killed = 'は餓狼の餌食になったようです';			//餓狼襲撃
  static public $possessed          = 'は誰かに憑依したようです';			//憑依
  static public $possessed_targeted = 'は憑狼に憑依されたようです';			//憑狼襲撃
  static public $possessed_reset    = 'は憑依から開放されたようです';			//憑依リセット
  static public $dream_killed       = 'は獏の餌食になったようです';			//夢食い
  static public $trapped            = 'は罠にかかって死亡したようです';			//罠
  static public $fox_dead           = 'は占い師に呪い殺されたようです';			//呪殺
  static public $cursed             = 'は呪詛に呪い殺されたようです';			//呪返し
  static public $hunted             = 'は狩人に狩られたようです';			//狩人の狩り
  static public $reporter_duty      = 'は人外を尾行してしまい、襲われたようです';	//ブン屋殉職
  static public $escaper_dead       = 'は逃亡に失敗したようです';			//逃亡失敗
  static public $poison_dead        = 'は毒に冒され死亡したようです';			//毒
  static public $vampire_killed     = 'は血を吸い尽くされたようです';			//吸血
  static public $assassin_killed    = 'は暗殺されたようです';				//暗殺
  static public $ogre_killed        = 'は鬼に攫われたようです';				//人攫い
  static public $priest_returned    = 'は天に帰ったようです';				//天人帰還
  static public $revive_success     = 'は生き返りました';				//蘇生成功
  static public $revive_failed      = 'の蘇生に失敗したようです';			//蘇生失敗
  static public $sacrifice          = 'は誰かの犠牲となって死亡したようです';		//身代わり
  static public $suicide            = 'は自決したようです';				//自決

  /* ショック死 */
  static public $chicken            = 'は小心者だったようです';			//小心者
  static public $rabbit             = 'はウサギだったようです';			//ウサギ
  static public $perverseness       = 'は天邪鬼だったようです';			//天邪鬼
  static public $flattery           = 'はゴマすりだったようです';		//ゴマすり
  static public $celibacy           = 'は独身貴族だったようです';		//独身貴族
  static public $nervy              = 'は自信家だったようです';			//自信家
  static public $androphobia        = 'は男性恐怖症だったようです';		//男性恐怖症
  static public $gynophobia         = 'は女性恐怖症だったようです';		//女性恐怖症
  static public $impatience         = 'は短気だったようです';			//短気
  static public $febris             = 'は熱病にかかったようです';		//熱病
  static public $frostbite          = 'は凍傷にかかったようです';		//凍傷
  static public $warrant            = 'は死の宣告を受けたようです';		//死の宣告
  static public $panelist           = 'は解答者 (不正解) だったようです';	//解答者
  static public $challenge          = 'は難題を解けなかったようです';		//難題
  static public $sealed             = 'は封印されたようです';			//封印師
  static public $drunk              = 'は神主に酔い潰されたようです';		//神主
  static public $jealousy           = 'は橋姫に妬まれたようです';		//橋姫
  static public $agitated           = 'は扇動に巻き込まれたようです';		//扇動者
  static public $followed           = 'は道連れにされたようです';		//舟幽霊
  static public $duel               = 'は宿敵に退治されたようです';		//決闘者
  static public $thunderbolt        = 'は落雷を受けたようです';			//青天の霹靂

  /* 特殊 */
  static public $novoted            = 'は突然お亡くなりになられました';		//未投票突然死
  static public $sudden_death       = 'はショック死しました';			//投票系ショック死
  static public $lovers_followed    = 'は恋人の後を追い自殺しました';		//恋人後追い
  static public $fox_followed       = 'は妖狐の後を追い自殺しました';		//背徳者後追い
  static public $vega_lovers        = 'は織姫に選ばれました';			//織姫
  static public $joker_moved        = 'にジョーカーが移動したようです';		//ジョーカー移動
  static public $death_note_moved   = 'にデスノートが移動したようです';		//デスノート移動
  static public $step               = 'で足音が聞こえた…';			//足音

  /* 花妖精 */
  static public $flowered_a = 'の頭の上に松の花が咲きました';
  static public $flowered_b = 'の頭の上に梅の花が咲きました';
  static public $flowered_c = 'の頭の上に桜の花が咲きました';
  static public $flowered_d = 'の頭の上に藤の花が咲きました';
  static public $flowered_e = 'の頭の上に菖蒲の花が咲きました';
  static public $flowered_f = 'の頭の上に牡丹の花が咲きました';
  static public $flowered_g = 'の頭の上に萩の花が咲きました';
  static public $flowered_h = 'の頭の上に芒の花が咲きました';
  static public $flowered_i = 'の頭の上に菊の花が咲きました';
  static public $flowered_j = 'の頭の上に紅葉の花が咲きました';
  static public $flowered_k = 'の頭の上に柳の花が咲きました';
  static public $flowered_l = 'の頭の上に桐の花が咲きました';
  static public $flowered_m = 'の頭の上に鬼灯の花が咲きました';
  static public $flowered_n = 'の頭の上に達磨草の花が咲きました';
  static public $flowered_o = 'の頭の上に福寿草の花が咲きました';
  static public $flowered_p = 'の頭の上に山茶花の花が咲きました';
  static public $flowered_q = 'の頭の上に彼岸花の花が咲きました';
  static public $flowered_r = 'の頭の上に鈴蘭の花が咲きました';
  static public $flowered_s = 'の頭の上に向日葵の花が咲きました';
  static public $flowered_t = 'の頭の上に優曇華の花が咲きました';
  static public $flowered_u = 'の頭の上に桃の花が咲きました';
  static public $flowered_v = 'の頭の上に椿の花が咲きました';
  static public $flowered_w = 'の頭の上に鳳仙花の花が咲きました';
  static public $flowered_x = 'の頭の上に薔薇の花が咲きました';
  static public $flowered_y = 'の頭の上に百合の花が咲きました';
  static public $flowered_z = 'の頭の上に仙人掌の花が咲きました';

  /* 星妖精 */
  static public $constellation_a = 'は昨夜、牡羊座を見ていたようです';
  static public $constellation_b = 'は昨夜、牡牛座を見ていたようです';
  static public $constellation_c = 'は昨夜、双子座を見ていたようです';
  static public $constellation_d = 'は昨夜、蟹座を見ていたようです';
  static public $constellation_e = 'は昨夜、獅子座を見ていたようです';
  static public $constellation_f = 'は昨夜、乙女座を見ていたようです';
  static public $constellation_g = 'は昨夜、天秤座を見ていたようです';
  static public $constellation_h = 'は昨夜、蠍座を見ていたようです';
  static public $constellation_i = 'は昨夜、射手座を見ていたようです';
  static public $constellation_j = 'は昨夜、山羊座を見ていたようです';
  static public $constellation_k = 'は昨夜、水瓶座を見ていたようです';
  static public $constellation_l = 'は昨夜、魚座を見ていたようです';
  static public $constellation_m = 'は昨夜、蛇遣座を見ていたようです';
  static public $constellation_n = 'は昨夜、牛飼座を見ていたようです';
  static public $constellation_o = 'は昨夜、琴座を見ていたようです';
  static public $constellation_p = 'は昨夜、白鳥座を見ていたようです';
  static public $constellation_q = 'は昨夜、鷲座を見ていたようです';
  static public $constellation_r = 'は昨夜、ペガスス座を見ていたようです';
  static public $constellation_s = 'は昨夜、アンドロメダ座を見ていたようです';
  static public $constellation_t = 'は昨夜、オリオン座を見ていたようです';
  static public $constellation_u = 'は昨夜、大犬座を見ていたようです';
  static public $constellation_v = 'は昨夜、子犬座を見ていたようです';
  static public $constellation_w = 'は昨夜、カシオペア座を見ていたようです';
  static public $constellation_x = 'は昨夜、竜座を見ていたようです';
  static public $constellation_y = 'は昨夜、鳳凰座を見ていたようです';
  static public $constellation_z = 'は昨夜、南十字座を見ていたようです';

  /* 道化師 */
  static public $pierrot_a = 'は昨夜、玉乗りをしていたようです';
  static public $pierrot_b = 'は昨夜、綱渡りをしていたようです';
  static public $pierrot_c = 'は昨夜、火の輪くぐりをしていたようです';
  static public $pierrot_d = 'は昨夜、パントマイムをしていたようです';
  static public $pierrot_e = 'は昨夜、ジャグリングをしていたようです';
  static public $pierrot_f = 'は昨夜、空中ブランコをしていたようです';
  static public $pierrot_g = 'は昨夜、ナイフ投げをしていたようです';
  static public $pierrot_h = 'は昨夜、必殺技の練習をしていたようです';
  static public $pierrot_i = 'は昨夜、自分の二つ名を考えていたようです';
  static public $pierrot_j = 'は昨夜、腕の包帯を抑えてうめいていたようです';
  static public $pierrot_k = 'は昨夜、筋トレをしていたようです';
  static public $pierrot_l = 'は昨夜、双眸に月輪を映していたようです';
  static public $pierrot_m = 'は昨夜、流れ星に祈りを捧げていたようです';
  static public $pierrot_n = 'は昨夜、鏡の中の自分をずっと見つめていたようです';
  static public $pierrot_o = 'は昨夜、輝いていたようです';
  static public $pierrot_p = 'は昨夜、ポエムを詠んでいたようです';
  static public $pierrot_q = 'は昨夜、ぬいぐるみに恋の悩みを相談していたようです';
  static public $pierrot_r = 'は昨夜、ラブレターを書いていたようです';
  static public $pierrot_s = 'は昨夜、枕を抱えてバタバタしていたようです';
  static public $pierrot_t = 'は昨夜、部屋の隅で泣いていたようです';
  static public $pierrot_u = 'は昨夜、死亡フラグが立っていたようです';
  static public $pierrot_v = 'は昨夜、タンスの角に小指をぶつけたようです';
  static public $pierrot_w = 'は昨夜、男装していたようです';
  static public $pierrot_x = 'は昨夜、女装していたようです';
  static public $pierrot_y = 'は昨夜、ブラフに引っかかったようです';
  static public $pierrot_z = 'は昨夜、命乞いの練習をしていたようです';
}
