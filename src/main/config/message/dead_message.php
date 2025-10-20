<?php
//-- 死亡メッセージ --//
class DeadMessage {
  /* 基本 */
  public static $vote_killed        = 'は投票の結果処刑されました';			//処刑
  public static $vote_cancelled     = 'の処刑は無かったことにされました';		//処刑キャンセル
  public static $blind_vote         = '傘化けの能力で投票結果が隠されました';		//傘化け
  public static $deadman            = 'は無残な姿で発見されました';			//共通死亡メッセージ
  public static $wolf_killed        = 'は人狼の餌食になったようです';			//人狼襲撃
  public static $hungry_wolf_killed = 'は餓狼の餌食になったようです';			//餓狼襲撃
  public static $possessed          = 'は誰かに憑依したようです';			//憑依
  public static $possessed_targeted = 'は憑狼に憑依されたようです';			//憑狼襲撃
  public static $possessed_reset    = 'は憑依から開放されたようです';			//憑依リセット
  public static $dream_killed       = 'は獏の餌食になったようです';			//夢食い
  public static $trapped            = 'は罠にかかって死亡したようです';			//罠
  public static $fox_dead           = 'は占い師に呪い殺されたようです';			//呪殺
  public static $cursed             = 'は呪詛に呪い殺されたようです';			//呪返し
  public static $hunted             = 'は狩人に狩られたようです';			//狩人の狩り
  public static $reporter_duty      = 'は人外を尾行してしまい、襲われたようです';	//ブン屋殉職
  public static $escaper_dead       = 'は逃亡に失敗したようです';			//逃亡失敗
  public static $poison_dead        = 'は毒に冒され死亡したようです';			//毒
  public static $vampire_killed     = 'は血を吸い尽くされたようです';			//吸血
  public static $assassin_killed    = 'は暗殺されたようです';				//暗殺
  public static $ogre_killed        = 'は鬼に攫われたようです';				//人攫い
  public static $tengu_killed       = 'は神隠しに遭ったようです';			//神隠し
  public static $priest_returned    = 'は天に帰ったようです';				//天人帰還
  public static $revive_success     = 'は生き返りました';				//蘇生成功
  public static $revive_failed      = 'の蘇生に失敗したようです';			//蘇生失敗
  public static $sacrifice          = 'は誰かの犠牲となって死亡したようです';		//身代わり
  public static $suicide            = 'は自決したようです';				//自決

  /* ショック死 */
  public static $chicken          = 'は小心者だったようです';		//小心者
  public static $critical_chicken = 'は魔女の一撃を受けたようです';	//魔女の一撃
  public static $rabbit           = 'はウサギだったようです';		//ウサギ
  public static $perverseness     = 'は天邪鬼だったようです';		//天邪鬼
  public static $flattery         = 'はゴマすりだったようです';		//ゴマすり
  public static $celibacy         = 'は独身貴族だったようです';		//独身貴族
  public static $nervy            = 'は自信家だったようです';		//自信家
  public static $androphobia      = 'は男性恐怖症だったようです';		//男性恐怖症
  public static $gynophobia       = 'は女性恐怖症だったようです';		//女性恐怖症
  public static $impatience       = 'は短気だったようです';		//短気
  public static $febris           = 'は熱病にかかったようです';		//熱病
  public static $frostbite        = 'は凍傷にかかったようです';		//凍傷
  public static $warrant          = 'は死の宣告を受けたようです';		//死の宣告
  public static $panelist         = 'は解答者 (不正解) だったようです';	//解答者
  public static $thorn            = 'は荊十字の刻印を刻まれたようです';	//荊十字
  public static $challenge        = 'は難題を解けなかったようです';	//難題
  public static $sealed           = 'は封印されたようです';		//封印師
  public static $drunk            = 'は神主に酔い潰されたようです';	//神主
  public static $jealousy         = 'は橋姫に妬まれたようです';		//橋姫
  public static $agitated         = 'は扇動に巻き込まれたようです';	//扇動者
  public static $followed         = 'は道連れにされたようです';		//舟幽霊
  public static $duel             = 'は宿敵に退治されたようです';		//決闘者
  public static $tengu_escape     = 'は雲隠れしたようです';		//天狗
  public static $thunderbolt      = 'は落雷を受けたようです';		//青天の霹靂

  /* 人狼襲撃失敗 */
  public static $wolf_failed_trap     = '人狼は罠に阻まれたようです';		//罠
  public static $wolf_failed_guard    = '人狼は護衛に阻まれたようです';		//護衛
  public static $wolf_failed_resist   = '人狼は耐性に阻まれたようです';		//襲撃先耐性
  public static $wolf_failed_ogre     = '人狼は鬼の耐性に阻まれたようです';	//鬼耐性
  public static $wolf_failed_exit     = '人狼は襲撃対象を見失ったようです';	//離脱能力者襲撃
  public static $wolf_failed_escaper  = '人狼は逃亡者を襲撃したようです';	//逃亡者襲撃
  public static $wolf_failed_wolf     = '人狼は人狼を襲撃したようです';		//人狼襲撃
  public static $wolf_failed_fox      = '人狼は妖狐を襲撃したようです';		//妖狐襲撃
  public static $wolf_failed_reaction = '人狼は能力に阻まれたようです';		//身代わり他
  public static $wolf_failed_action   = '人狼は襲撃を回避したようです';		//人狼能力

  /* 特殊 */
  public static $novoted               = 'は突然お亡くなりになられました';		//未投票突然死
  public static $silence               = 'は沈黙していたようです';			//未発言突然死
  public static $force_sudden_death    = 'は強制的に突然死させられました';		//強制突然死
  public static $sudden_death          = 'はショック死しました';			//投票系ショック死
  public static $lovers_followed       = 'は恋人の後を追い自殺しました';		//恋人後追い
  public static $fox_followed          = 'は妖狐の後を追い自殺しました';		//背徳者後追い
  public static $vega_lovers           = 'は織姫に選ばれました';			//織姫
  public static $joker_moved           = 'にジョーカーが移動したようです';		//ジョーカー移動
  public static $death_note_moved      = 'にデスノートが移動したようです';		//デスノート移動
  public static $letter_exchange_moved = 'に交換日記が移動したようです';		//交換日記移動
  public static $step                  = 'で足音が聞こえた…';				//足音
  public static $active_critical_voter = 'は会心が発動したようです';			//会心発動
  public static $active_critical_luck  = 'は痛恨が発動したようです';			//痛恨発動
  public static $gender_status         = 'は今日一日性転換するようです';		//性転換発動
  public static $copied_trick          = 'は役職を奪われたようです';			//奇術コピー発動

  /* 花妖精 */
  public static $flowered_a = 'の頭の上に松の花が咲きました';
  public static $flowered_b = 'の頭の上に梅の花が咲きました';
  public static $flowered_c = 'の頭の上に桜の花が咲きました';
  public static $flowered_d = 'の頭の上に藤の花が咲きました';
  public static $flowered_e = 'の頭の上に菖蒲の花が咲きました';
  public static $flowered_f = 'の頭の上に牡丹の花が咲きました';
  public static $flowered_g = 'の頭の上に萩の花が咲きました';
  public static $flowered_h = 'の頭の上に芒の花が咲きました';
  public static $flowered_i = 'の頭の上に菊の花が咲きました';
  public static $flowered_j = 'の頭の上に紅葉の花が咲きました';
  public static $flowered_k = 'の頭の上に柳の花が咲きました';
  public static $flowered_l = 'の頭の上に桐の花が咲きました';
  public static $flowered_m = 'の頭の上に鬼灯の花が咲きました';
  public static $flowered_n = 'の頭の上に達磨草の花が咲きました';
  public static $flowered_o = 'の頭の上に福寿草の花が咲きました';
  public static $flowered_p = 'の頭の上に山茶花の花が咲きました';
  public static $flowered_q = 'の頭の上に彼岸花の花が咲きました';
  public static $flowered_r = 'の頭の上に鈴蘭の花が咲きました';
  public static $flowered_s = 'の頭の上に向日葵の花が咲きました';
  public static $flowered_t = 'の頭の上に優曇華の花が咲きました';
  public static $flowered_u = 'の頭の上に桃の花が咲きました';
  public static $flowered_v = 'の頭の上に椿の花が咲きました';
  public static $flowered_w = 'の頭の上に鳳仙花の花が咲きました';
  public static $flowered_x = 'の頭の上に薔薇の花が咲きました';
  public static $flowered_y = 'の頭の上に百合の花が咲きました';
  public static $flowered_z = 'の頭の上に仙人掌の花が咲きました';

  /* 星妖精 */
  public static $constellation_a = 'は昨夜、牡羊座を見ていたようです';
  public static $constellation_b = 'は昨夜、牡牛座を見ていたようです';
  public static $constellation_c = 'は昨夜、双子座を見ていたようです';
  public static $constellation_d = 'は昨夜、蟹座を見ていたようです';
  public static $constellation_e = 'は昨夜、獅子座を見ていたようです';
  public static $constellation_f = 'は昨夜、乙女座を見ていたようです';
  public static $constellation_g = 'は昨夜、天秤座を見ていたようです';
  public static $constellation_h = 'は昨夜、蠍座を見ていたようです';
  public static $constellation_i = 'は昨夜、射手座を見ていたようです';
  public static $constellation_j = 'は昨夜、山羊座を見ていたようです';
  public static $constellation_k = 'は昨夜、水瓶座を見ていたようです';
  public static $constellation_l = 'は昨夜、魚座を見ていたようです';
  public static $constellation_m = 'は昨夜、蛇遣座を見ていたようです';
  public static $constellation_n = 'は昨夜、牛飼座を見ていたようです';
  public static $constellation_o = 'は昨夜、琴座を見ていたようです';
  public static $constellation_p = 'は昨夜、白鳥座を見ていたようです';
  public static $constellation_q = 'は昨夜、鷲座を見ていたようです';
  public static $constellation_r = 'は昨夜、ペガスス座を見ていたようです';
  public static $constellation_s = 'は昨夜、アンドロメダ座を見ていたようです';
  public static $constellation_t = 'は昨夜、オリオン座を見ていたようです';
  public static $constellation_u = 'は昨夜、大犬座を見ていたようです';
  public static $constellation_v = 'は昨夜、子犬座を見ていたようです';
  public static $constellation_w = 'は昨夜、カシオペア座を見ていたようです';
  public static $constellation_x = 'は昨夜、竜座を見ていたようです';
  public static $constellation_y = 'は昨夜、鳳凰座を見ていたようです';
  public static $constellation_z = 'は昨夜、南十字座を見ていたようです';

  /* 道化師 */
  public static $pierrot_a = 'は昨夜、玉乗りをしていたようです';
  public static $pierrot_b = 'は昨夜、綱渡りをしていたようです';
  public static $pierrot_c = 'は昨夜、火の輪くぐりをしていたようです';
  public static $pierrot_d = 'は昨夜、パントマイムをしていたようです';
  public static $pierrot_e = 'は昨夜、ジャグリングをしていたようです';
  public static $pierrot_f = 'は昨夜、空中ブランコをしていたようです';
  public static $pierrot_g = 'は昨夜、ナイフ投げをしていたようです';
  public static $pierrot_h = 'は昨夜、必殺技の練習をしていたようです';
  public static $pierrot_i = 'は昨夜、自分の二つ名を考えていたようです';
  public static $pierrot_j = 'は昨夜、腕の包帯を抑えてうめいていたようです';
  public static $pierrot_k = 'は昨夜、筋トレをしていたようです';
  public static $pierrot_l = 'は昨夜、双眸に月輪を映していたようです';
  public static $pierrot_m = 'は昨夜、流れ星に祈りを捧げていたようです';
  public static $pierrot_n = 'は昨夜、鏡の中の自分をずっと見つめていたようです';
  public static $pierrot_o = 'は昨夜、輝いていたようです';
  public static $pierrot_p = 'は昨夜、ポエムを詠んでいたようです';
  public static $pierrot_q = 'は昨夜、ぬいぐるみに恋の悩みを相談していたようです';
  public static $pierrot_r = 'は昨夜、ラブレターを書いていたようです';
  public static $pierrot_s = 'は昨夜、枕を抱えてバタバタしていたようです';
  public static $pierrot_t = 'は昨夜、部屋の隅で泣いていたようです';
  public static $pierrot_u = 'は昨夜、死亡フラグが立っていたようです';
  public static $pierrot_v = 'は昨夜、タンスの角に小指をぶつけたようです';
  public static $pierrot_w = 'は昨夜、男装していたようです';
  public static $pierrot_x = 'は昨夜、女装していたようです';
  public static $pierrot_y = 'は昨夜、ブラフに引っかかったようです';
  public static $pierrot_z = 'は昨夜、命乞いの練習をしていたようです';
}
