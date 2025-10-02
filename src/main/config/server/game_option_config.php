<?php
//-- ゲームオプション設定 --//
class GameOptionConfig {
  //-- 基本設定 --//
  public static $wish_role_enable  = true; //役割希望制
  public static $default_wish_role = false;

  public static $real_time_enable  = true; //リアルタイム制 (初期設定は TimeConfig::DEFAULT_DAY/NIGHT 参照)
  public static $default_real_time = true;

  public static $open_vote_enable  = true; //投票した票数を公表する
  public static $default_open_vote = false;

  public static $settle_enable  = true; //決着村
  public static $default_settle = false;

  public static $seal_message_enable  = true; //天啓封印
  public static $default_seal_message = false;

  public static $open_day_enable  = true; //オープニングあり
  public static $default_open_day = false;

  public static $necessary_name_enable  = true; //ユーザ名必須
  public static $default_necessary_name = false;

  public static $necessary_trip_enable  = true; //トリップ必須
  public static $default_necessary_trip = false;

  public static $close_room_enable  = true; //募集停止
  public static $default_close_room = false;

  //-- 身代わり君設定 --//
  public static $dummy_boy_enable = true; //初日の夜は身代わり君
  //身代わり君のデフォルト ['':身代わり君無し / 'on':身代わり君有り / 'gm_login': GM 有り ]
  public static $default_dummy_boy = 'on';

  public static $gm_login_enable = true; //GM 有り

  public static $gm_logout_enable = true; //GM ログアウト (GM → 身代わり君有り)

  public static $gerd_enable  = true; //ゲルト君モード
  public static $default_gerd = false;

  //-- 会話設定 --//
  public static $wait_morning_enable  = true; //早朝待機制 (待機時間設定は TimeConfig::WAIT_MORNING 参照)
  public static $default_wait_morning = false;

  public static $limit_last_words_enable  = true; //遺言制限
  public static $default_limit_last_words = false;

  public static $limit_talk_enable  = true; //発言数制限制 (初期設定は GameConfig::LIMIT_TALK_COUNT 参照)
  public static $default_limit_talk = false;

  public static $secret_talk_enable  = true; //秘密会話あり
  public static $default_secret_talk = false;

  public static $no_silence_enable  = true; //沈黙禁止
  public static $default_no_silence = false;

  //-- 霊界公開設定 --//
  public static $not_open_cast_enable  = true; //霊界で配役を公開しない
  public static $auto_open_cast_enable = true; //霊界で配役を自動で公開する

  //霊界オフモードのデフォルト ['':無し / 'auto_open_cast':自動オフ / 'not_open_cast': 完全オフ ]
  public static $default_not_open_cast = 'auto_open_cast';

  //-- 追加役職設定 --//
  //必要人数は CastConfig の同名オプション参照 (例： $poison_enable => CastConfig::$poison)
  public static $poison_enable  = true; //埋毒者
  public static $default_poison = true;

  public static $assassin_enable  = true; //暗殺者
  public static $default_assassin = false;

  public static $wolf_enable  = true; //人狼追加
  public static $default_wolf = false;

  public static $boss_wolf_enable  = true; //白狼
  public static $default_boss_wolf = false;

  public static $poison_wolf_enable  = true; //毒狼
  public static $default_poison_wolf = false;

  public static $tongue_wolf_enable  = true; //舌禍狼
  public static $default_tongue_wolf = false;

  public static $possessed_wolf_enable  = true; //憑狼
  public static $default_possessed_wolf = false;

  public static $sirius_wolf_enable  = true; //天狼
  public static $default_sirius_wolf = false;

  public static $mad_enable  = true; //狂人追加
  public static $default_mad = false;

  public static $fox_enable  = true; //妖狐追加
  public static $default_fox = false;

  public static $no_fox_enable  = true; //妖狐なし
  public static $default_no_fox = false;

  public static $child_fox_enable  = true; //子狐
  public static $default_child_fox = false;

  public static $depraver_enable  = true; //背徳者
  public static $default_depraver = false;

  public static $cupid_enable  = true; //キューピッド
  public static $default_cupid = false;

  public static $medium_enable  = true; //巫女
  public static $default_medium = false;

  public static $mania_enable  = true; //神話マニア
  public static $default_mania = false;

  public static $decide_enable  = true; //決定者
  public static $default_decide = false;

  public static $authority_enable  = true; //権力者
  public static $default_authority = false;

  //-- 特殊村設定 --//
  public static $detective_enable  = true; //探偵村
  public static $default_detective = false;

  public static $liar_enable  = true; //狼少年村
  public static $default_liar = false;

  public static $gentleman_enable  = true; //紳士・淑女村
  public static $default_gentleman = false;

  public static $passion_enable  = true; //恋色迷彩村
  public static $default_passion = false;

  public static $deep_sleep_enable  = true; //静寂村
  public static $default_deep_sleep = false;

  public static $blinder_enable  = true; //宵闇村
  public static $default_blinder = false;

  public static $mind_open_enable  = true; //白夜村
  public static $default_mind_open = false;

  public static $critical_enable  = true; //急所村
  public static $default_critical = false;

  public static $notice_critical_enable  = true; //急所通知
  public static $default_notice_critical = false;

  public static $sudden_death_enable  = true; //虚弱体質村
  public static $default_sudden_death = false;

  public static $perverseness_enable  = true; //天邪鬼村
  public static $default_perverseness = false;

  public static $joker_enable  = true; //ババ抜き村
  public static $default_joker = false;

  public static $death_note_enable  = true; //デスノート村
  public static $default_death_note = false;

  public static $weather_enable  = true; //天候あり
  public static $default_weather = false;

  public static $full_weather_enable  = true; //天変地異
  public static $default_full_weather = false;

  public static $festival_enable  = true; //お祭り村
  public static $default_festival = false;

  public static $replace_human_enable      = true; //村人置換村 (管理人カスタムモード)
  public static $full_mad_enable           = true; //狂人村
  public static $full_cupid_enable         = true; //キューピッド村
  public static $full_quiz_enable          = true; //出題者村
  public static $full_vampire_enable       = true; //吸血鬼村
  public static $full_chiroptera_enable    = true; //蝙蝠村
  public static $full_chiroptera_patron    = true; //後援者村
  public static $full_mania_enable         = true; //神話マニア村
  public static $full_unknown_mania_enable = true; //鵺村
  //村人置換モードの内訳
  public static $replace_human_selector_list = [
    '' => 'なし', 'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire',
    'full_chiroptera', 'full_patron', 'full_mania', 'full_unknown_mania'
  ];

  public static $change_common_enable        = true; //共有者置換村 (管理人カスタムモード)
  public static $change_hermit_common_enable = true; //隠者村
  //共有者置換モードの内訳
  public static $change_common_selector_list = [
    '' => 'なし', 'change_common', 'change_hermit_common'
  ];

  public static $change_mad_enable          = true; //狂人置換村 (管理人カスタムモード)
  public static $change_fanatic_mad_enable  = true; //狂信者村
  public static $change_whisper_mad_enable  = true; //囁き狂人村
  public static $change_immolate_mad_enable = true; //殉教者村
  //狂人置換モードの内訳
  public static $change_mad_selector_list = [
    '' => 'なし', 'change_mad', 'change_fanatic_mad', 'change_whisper_mad', 'change_immolate_mad'
  ];

  public static $change_cupid_enable          = true; //キューピッド置換村 (管理人カスタムモード)
  public static $change_mind_cupid_enable     = true; //女神村
  public static $change_triangle_cupid_enable = true; //小悪魔村
  public static $change_angel_enable          = true; //天使村
  //キューピッド置換モードの内訳
  public static $change_cupid_selector_list = [
    '' => 'なし', 'change_cupid', 'change_mind_cupid', 'change_triangle_cupid', 'change_angel',
    'change_exchange_angel'
  ];

  //-- 特殊配役モード --//
  public static $chaos_enable       = true; //闇鍋モード
  public static $chaosfull_enable   = true; //真・闇鍋モード
  public static $chaos_hyper_enable = true; //超・闇鍋モード
  public static $chaos_verso_enable = true; //裏・闇鍋モード
  public static $duel               = true; //決闘村
  public static $gray_random_enable = true; //グレラン村
  public static $step_enable        = true; //足音村
  public static $quiz_enable        = true; //クイズ村
  //特殊配役モードの内訳
  public static $special_role_list = [
    '' => 'なし', 'chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'duel', 'gray_random',
    'step', 'quiz'
  ];

  //-- 闇鍋モード専用設定 --//
  public static $topping_enable = true; //固定配役追加モード
  public static $topping_list = [
    ''  => 'なし',
    'a' => 'A：人形村',
    'b' => 'B：出題村',
    'c' => 'C：吸血村',
    'd' => 'D：蘇生村',
    'e' => 'E：憑依村',
    'f' => 'F：鬼村',
    'g' => 'G：嘘吐村',
    'h' => 'H：村人村',
    'i' => 'I：恋人村',
    'j' => 'J：宿敵村',
    'k' => 'K：覚醒村',
    'l' => 'L：白銀村',
    'm' => 'M：暗殺村',
    'n' => 'N：罠村',
    'o' => 'O：陰陽村',
    'p' => 'P：音鳴村',
    'q' => 'Q：雛村',
    'r' => 'R：妖精村',
    's' => 'S：霊能村',
    't' => 'T：天狗村',
    'u' => 'U：背信村'
  ];

  public static $boost_rate_enable = true; //出現率変動モード
  public static $boost_rate_list = [
    ''  => 'なし',
    'a' => 'A：新顔村',
    'b' => 'B：平等村',
    'c' => 'C：派生村',
    'd' => 'D：封蘇村',
    'e' => 'E：封憑村',
    'f' => 'F：合戦村',
    'g' => 'G：独身村',
    'h' => 'H：無毒村',
    'i' => 'I：強心村',
    'j' => 'J：封呪村',
    'k' => 'K：封夢村',
    'l' => 'L：結束村',
    'm' => 'M：天道村'
  ];

  //配役通知設定
  public static $chaos_open_cast_enable      = true; //配役内訳を表示する
  public static $chaos_open_cast_camp_enable = true; //陣営通知
  public static $chaos_open_cast_role_enable = true; //役職通知
  //通知モードのデフォルト ['':無し / 'camp':陣営 / 'role':役職 / 'full':完全]
  public static $default_chaos_open_cast = 'camp'; //陣営通知

  //サブ役職制限
  public static $sub_role_limit_enable             = true; //サブ役職制限
  public static $sub_role_limit_easy_enable        = true; //サブ役職制限：EASYモード
  public static $sub_role_limit_normal_enable      = true; //サブ役職制限：NORMALモード
  public static $sub_role_limit_hard_enable        = true; //サブ役職制限：HARDモード
  public static $sub_role_limit_no_sub_role_enable = true; //サブ役職をつけない
  //サブ役職制限のデフォルト
  //['':制限無し / 'no_sub':サブ役職をつけない / 'easy':EASYモード / 'normal':NORMALモード / 'hard':HARDモード]
  public static $default_sub_role_limit = 'no_sub_role'; //つけない

  //その他
  public static $secret_sub_role_enable  = true; //サブ役職を本人に通知しない
  public static $default_secret_sub_role = false;
}
