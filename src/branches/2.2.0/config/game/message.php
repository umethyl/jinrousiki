<?php
//-- 基本システムメッセージ --//
class Message {
  /* 身代わり君 */
  static public $dummy_boy            = '◆身代わり君　'; //仮想 GM 発言ヘッダー
  static public $dummy_boy_comment    = '僕はおいしくないよ'; //コメント
  static public $dummy_boy_last_words = '僕はおいしくないって言ったのに……'; //遺言

  /* ユーザ登録 */
  //入村メッセージ
  static public $entry_user = ' が村の集会場にやってきました';

  /* 特殊通知メッセージ */
  static public $vote_announce = '時間がありません。投票してください。'; //会話の制限時間切れ
  static public $wait_morning  = '待機時間中です。'; //早朝待機制の待機時間中
  static public $close_cast    = '配役隠蔽中です。'; //配役隠蔽通知 (霊界自動公開モード用)

  /* 投票 */
  static public $vote_reset = '＜投票がリセットされました　再度投票してください＞'; //投票リセット
  static public $kick_out   = 'は席をあけわたし、村から去りました'; //Kick 処理

  /* 再投票 */
  static public $revote        = '再投票となりました'; //投票結果
  static public $draw_announce = '再投票となると引き分けになります'; //引き分け告知

  /* 未投票処理 */
  static public $sudden_death_announce = '投票完了されない方は死して地獄へ堕ちてしまいます'; //警告
  static public $sudden_death_time     = '突然死になるまで後：'; //期限
  static public $sudden_death          = 'は突然お亡くなりになられました'; //突然死処理

  /* 発言 */
  static public $say_limit = '文字数または行数が多すぎたので発言できませんでした'; //発言数上限
  static public $silence   = 'ほどの沈黙が続いた'; //沈黙判定 (会話で時間経過制)

  /* 遠吠え・囁き */
  static public $wolf_howl   = 'アオォーン・・・'; //人狼の遠吠え
  static public $common_talk = 'ヒソヒソ・・・'; //共有者の囁き
  static public $lovers_talk = 'うふふ・・・うふふ・・・'; //恋人の囁き
  static public $howling     = 'キィーーン・・・'; //スピーカーの音割れ効果音

  /* 発言置換能力者 */
  static public $cute_wolf = ''; //不審者・萌系 (空なら人狼の遠吠えになる)
  static public $gentleman = "お待ち下さい。\n%sさん、ハンケチーフを落としておりますぞ。";  //紳士
  static public $lady      = "お待ちなさい！\n%s、タイが曲がっていてよ。"; //淑女

  /* シーン切り替え */
  static public $morning    = '朝日が昇り、%s 日目の朝がやってきました'; //朝
  static public $night      = '日が落ち、暗く静かな夜がやってきました'; //夜
  static public $chaos      = '配役隠蔽モードです'; //配役隠蔽通知 (闇鍋用)
  static public $skip_night = '白澤の能力で夜が飛ばされました……'; //白澤の能力発動

  /* ランダムメッセージ挿入 */
  //GameConfig::RANDOM_MESSAGE を true にすると、この配列の中身がランダムに表示される
  static public $random_message_list = array();

  /* 遺言 */
  static public $lastwords = '夜が明けると前の日に亡くなった方の遺言書が見つかりました';

  /* 死亡メッセージ */
  static public $vote_killed        = 'は投票の結果処刑されました'; //処刑
  static public $blind_vote         = '傘化けの能力で投票結果が隠されました'; //傘化け
  static public $deadman            = 'は無残な姿で発見されました'; //共通死亡メッセージ
  static public $wolf_killed        = 'は人狼の餌食になったようです'; //人狼襲撃
  static public $hungry_wolf_killed = 'は餓狼の餌食になったようです'; //餓狼襲撃
  static public $possessed          = 'は誰かに憑依したようです'; //憑依
  static public $possessed_targeted = 'は憑狼に憑依されたようです'; //憑狼襲撃
  static public $possessed_reset    = 'は憑依から開放されたようです'; //憑依リセット
  static public $dream_killed       = 'は獏の餌食になったようです'; //夢食い
  static public $trapped            = 'は罠にかかって死亡したようです'; //罠
  static public $fox_dead           = 'は占い師に呪い殺されたようです'; //呪殺
  static public $cursed             = 'は呪詛に呪い殺されたようです'; //呪返し
  static public $hunted             = 'は狩人に狩られたようです'; //狩人の狩り
  static public $reporter_duty      = 'は人外を尾行してしまい、襲われたようです'; //ブン屋殉職
  static public $escaper_dead       = 'は逃亡に失敗したようです'; //逃亡失敗
  static public $poison_dead        = 'は毒に冒され死亡したようです'; //毒
  static public $vampire_killed     = 'は血を吸い尽くされたようです'; //吸血
  static public $assassin_killed    = 'は暗殺されたようです'; //暗殺
  static public $ogre_killed        = 'は鬼に攫われたようです'; //人攫い
  static public $priest_returned    = 'は天に帰ったようです'; //天人帰還
  static public $revive_success     = 'は生き返りました'; //蘇生成功
  static public $revive_failed      = 'の蘇生に失敗したようです'; //蘇生失敗
  static public $sacrifice          = 'は誰かの犠牲となって死亡したようです'; //身代わり
  static public $lovers_followed    = 'は恋人の後を追い自殺しました'; //恋人後追い
  static public $vote_sudden_death  = 'はショック死しました'; //投票系ショック死
  static public $novoted            = 'は突然お亡くなりになられました'; //未投票突然死
  static public $chicken            = 'は小心者だったようです'; //小心者
  static public $rabbit             = 'はウサギだったようです'; //ウサギ
  static public $perverseness       = 'は天邪鬼だったようです'; //天邪鬼
  static public $flattery           = 'はゴマすりだったようです'; //ゴマすり
  static public $celibacy           = 'は独身貴族だったようです'; //独身貴族
  static public $nervy              = 'は自信家だったようです'; //自信家
  static public $androphobia        = 'は男性恐怖症だったようです'; //男性恐怖症
  static public $gynophobia         = 'は女性恐怖症だったようです'; //女性恐怖症
  static public $impatience         = 'は短気だったようです'; //短気
  static public $febris             = 'は熱病にかかったようです'; //熱病
  static public $frostbite          = 'は凍傷にかかったようです'; //凍傷
  static public $warrant            = 'は死の宣告を受けたようです'; //死の宣告
  static public $panelist           = 'は解答者 (不正解) だったようです'; //解答者
  static public $challenge          = 'は難題を解けなかったようです'; //難題
  static public $sealed             = 'は封印されたようです'; //封印師
  static public $drunk              = 'は神主に酔い潰されたようです'; //神主
  static public $jealousy           = 'は橋姫に妬まれたようです'; //橋姫
  static public $agitated           = 'は扇動に巻き込まれたようです'; //扇動者
  static public $followed           = 'は道連れにされたようです'; //舟幽霊
  static public $duel               = 'は宿敵に退治されたようです'; //決闘者
  static public $thunderbolt        = 'は落雷を受けたようです'; //青天の霹靂
  static public $joker_moved        = 'にジョーカーが移動したようです'; //ジョーカーの移動
  static public $death_note_moved   = 'にデスノートが移動したようです'; //デスノートの移動
  static public $step               = 'で足音が聞こえた…'; //足音

  /* 花妖精のリスト (A-Z) */
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

  /* 星妖精のリスト (A-Z) */
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

  /* 道化師のリスト (A-Z) */
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

  /* 投票ログ */
  //static public $game_start        = ' はゲーム開始投票をしました'; //ゲーム開始投票 (現在は不使用)
  static public $objection         = ' が「異議」を申し立てました'; //「異議」あり
  static public $kick_do           = ' に KICK 投票しました'; //KICK 投票
  static public $vote_do           = ' に処刑投票しました'; //処刑投票
  static public $wolf_eat          = ' に狙いをつけました'; //人狼の投票
  static public $silent_wolf_eat   = ' に静かに狙いをつけました'; //響狼のステルス投票
  static public $escape_do         = ' の周辺に逃亡しました'; //逃亡者の投票
  static public $mage_do           = ' を占います'; //占い師の投票
  static public $voodoo_killer_do  = ' の呪いを祓います'; //陰陽師の投票
  static public $jammer_do         = ' の占いを妨害します'; //月兎の投票
  static public $trap_do           = ' の周辺に罠を仕掛けました'; //罠師の投票
  static public $trap_not_do       = ' は罠設置を行いませんでした'; //罠師のキャンセル投票
  static public $possessed_do      = ' に憑依します'; //犬神の投票
  static public $possessed_not_do  = ' は憑依を行いませんでした'; //犬神のキャンセル投票
  static public $voodoo_do         = ' に呪いをかけます'; //呪術師の投票
  static public $dream_eat         = ' に狙いをつけました'; //獏の投票
  static public $step_do           = ' の周辺を徘徊します'; //家鳴の投票
  static public $step_not_do       = ' は徘徊を行いませんでした'; //家鳴のキャンセル投票
  static public $guard_do          = ' の護衛に付きました'; //狩人の投票
  static public $anti_voodoo_do    = ' の厄を祓います'; //厄神の投票
  static public $reporter_do       = ' を尾行しました'; //ブン屋の投票
  static public $revive_do         = ' に蘇生処置をしました'; //猫又の投票
  static public $revive_not_do     = ' は蘇生処置をしませんでした'; //猫又のキャンセル投票
  static public $assassin_do       = ' に狙いをつけました'; //暗殺者の投票
  static public $assassin_not_do   = ' は暗殺を行いませんでした'; //暗殺者のキャンセル投票
  static public $mind_scanner_do   = ' の心を読みます'; //さとりの投票
  static public $wizard_do         = ' に魔法をかけました'; //魔法使いの投票
  static public $cupid_do          = ' に愛の矢を放ちました'; //キューピッドの投票
  static public $vampire_do        = ' に狙いをつけました'; //吸血鬼の投票
  static public $fairy_do          = ' に悪戯しました'; //妖精の投票
  static public $ogre_do           = ' に狙いをつけました'; //鬼の投票
  static public $ogre_not_do       = ' は人攫いを行いませんでした'; //鬼のキャンセル投票
  static public $duelist_do        = ' に宿命を結び付けました'; //決闘者の投票
  static public $mania_do          = ' の能力を真似ることにしました'; //神話マニアの投票
  static public $death_note_do     = ' の名前を書きました'; //デスノートの投票
  static public $death_note_not_do = ' はデスノートを使いませんでした'; //デスノートのキャンセル投票

  /* 能力の表示 */
  static public $ability_dead             = 'アナタは息絶えました・・・'; //死者
  static public $ability_vote             = '処刑する人を選択してください'; //処刑
  static public $ability_wolf_eat         = '喰い殺す人を選択してください'; //人狼
  static public $ability_mage_do          = '占う人を選択してください'; //占い師
  static public $ability_voodoo_killer_do = '呪いを祓う人を選択してください'; //陰陽師
  static public $ability_guard_do         = '護衛する人を選択してください'; //狩人
  static public $ability_reporter_do      = '尾行する人を選択してください'; //ブン屋
  static public $ability_anti_voodoo_do   = '厄を祓う人を選択してください'; //厄神
  static public $ability_revive_do        = '蘇生する人を選択してください'; //猫又
  static public $ability_assassin_do      = '暗殺する人を選択してください'; //暗殺者
  static public $ability_mind_scanner_do  = '心を読む人を選択してください'; //さとり
  static public $ability_wizard_do        = '魔法をかける人を選択してください'; //魔法使い
  static public $ability_escape_do        = '逃亡する先を選択してください'; //逃亡者
  static public $ability_jammer_do        = '占いを妨害する人を選択してください'; //月兎
  static public $ability_voodoo_do        = '呪いをかける人を選択してください'; //呪術師
  static public $ability_step_do          = '徘徊する人を選択してください'; //家鳴
  static public $ability_dream_eat        = '夢を食べる人を選択してください'; //獏
  static public $ability_possessed_do     = '憑依する人を選択してください'; //犬神
  static public $ability_trap_do          = '罠を設置する先を選択してください'; //罠師
  static public $ability_cupid_do         = '結びつける人を選択してください'; //キューピッド
  static public $ability_vampire_do       = '吸血する人を選択してください'; //吸血鬼
  static public $ability_fairy_do         = '悪戯する人を選択してください'; //妖精
  static public $ability_ogre_do          = '攫う人を選択してください'; //鬼
  static public $ability_duelist_do       = '結びつける人を選択してください'; //決闘者
  static public $ability_mania_do         = '能力を真似る人を選択してください'; //神話マニア
  static public $ability_death_note_do    = '名前を書く人を選択してください'; //デスノート
}
