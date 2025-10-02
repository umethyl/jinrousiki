<?php
//-- ゲームオプション設定 --//
class GameOptionConfig {
  //-- 基本設定 --//
  static $wish_role_enable  = true; //役割希望制
  static $default_wish_role = false;

  static $real_time_enable  = true; //リアルタイム制 (初期設定は TimeConfig->default_day/night 参照)
  static $default_real_time = true;

  static $wait_morning_enable  = true; //早朝待機制 (待機時間設定は TimeConfig->wait_morning 参照)
  static $default_wait_morning = false;

  static $open_vote_enable  = true; //投票した票数を公表する
  static $default_open_vote = false;

  static $settle_enable  = true; //決着村
  static $default_settle = false;

  static $seal_message_enable  = true; //天啓封印
  static $default_seal_message = false;

  static $open_day_enable  = true; //オープニングあり
  static $default_open_day = false;

  static $necessary_name_enable  = true; //ユーザ名必須
  static $default_necessary_name = false;

  static $necessary_trip_enable  = true; //トリップ必須
  static $default_necessary_trip = false;

  //-- 身代わり君設定 --//
  static $dummy_boy_enable = true; //初日の夜は身代わり君
  //身代わり君のデフォルト ['':身代わり君無し / 'on':身代わり君有り / 'gm_login': GM有り ]
  static $default_dummy_boy = 'on';

  static $gerd_enable  = true; //ゲルト君モード
  static $default_gerd = false;

  //-- 霊界公開設定 --//
  static $not_open_cast_enable  = true; //霊界で配役を公開しない
  static $auto_open_cast_enable = true; //霊界で配役を自動で公開する

  //霊界オフモードのデフォルト ['':無し / 'auto_open_cast':自動オフ / 'not_open_cast': 完全オフ ]
  static $default_not_open_cast = 'auto_open_cast';

  //-- 追加役職設定 --//
  //必要人数は CastConfig の同名オプション名参照 (例： $poison_enable => CastConfig->poison)
  static $poison_enable  = true; //埋毒者
  static $default_poison = true;

  static $assassin_enable  = true; //暗殺者
  static $default_assassin = false;

  static $wolf_enable  = true; //人狼追加
  static $default_wolf = false;

  static $boss_wolf_enable  = true; //白狼
  static $default_boss_wolf = false;

  static $poison_wolf_enable  = true; //毒狼
  static $default_poison_wolf = false;

  static $tongue_wolf_enable  = true; //舌禍狼
  static $default_tongue_wolf = false;

  static $possessed_wolf_enable  = true; //憑狼
  static $default_possessed_wolf = false;

  static $sirius_wolf_enable  = true; //天狼
  static $default_sirius_wolf = false;

  static $fox_enable  = true; //妖狐追加
  static $default_fox = false;

  static $child_fox_enable  = true; //子狐
  static $default_child_fox = false;

  static $cupid_enable  = true; //キューピッド
  static $default_cupid = false;

  static $medium_enable  = true; //巫女
  static $default_medium = false;

  static $mania_enable  = true; //神話マニア
  static $default_mania = false;

  static $decide_enable  = true; //決定者
  static $default_decide = false;

  static $authority_enable  = true; //権力者
  static $default_authority = false;

  //-- 特殊村設定 --//
  static $detective_enable  = true; //探偵村
  static $default_detective = false;

  static $liar_enable  = true; //狼少年村
  static $default_liar = false;

  static $gentleman_enable  = true; //紳士・淑女村
  static $default_gentleman = false;

  static $deep_sleep_enable  = true; //静寂村
  static $default_deep_sleep = false;

  static $blinder_enable  = true; //宵闇村
  static $default_blinder = false;

  static $mind_open_enable  = true; //白夜村
  static $default_mind_open = false;

  static $critical_enable  = true; //急所村
  static $default_critical = false;

  static $sudden_death_enable  = true; //虚弱体質村
  static $default_sudden_death = false;

  static $perverseness_enable  = true; //天邪鬼村
  static $default_perverseness = false;

  static $joker_enable  = true; //ババ抜き村
  static $default_joker = false;

  static $death_note_enable  = true; //デスノート村
  static $default_death_note = false;

  static $weather_enable  = true; //天候あり
  static $default_weather = false;

  static $festival_enable  = true; //お祭り村
  static $default_festival = false;

  static $replace_human_enable      = true; //村人置換村 (管理人カスタムモード)
  static $full_mad_enable           = true; //狂人村
  static $full_cupid_enable         = true; //キューピッド村
  static $full_quiz_enable          = true; //出題者村
  static $full_vampire_enable       = true; //吸血鬼村
  static $full_chiroptera_enable    = true; //蝙蝠村
  static $full_mania_enable         = true; //神話マニア村
  static $full_unknown_mania_enable = true; //鵺村
  //村人置換モードの内訳
  static $replace_human_selector_list = array(
    '' => 'なし', 'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire',
    'full_chiroptera', 'full_mania', 'full_unknown_mania');

  static $change_common_enable        = true; //共有者置換村 (管理人カスタムモード)
  static $change_hermit_common_enable = true; //隠者村
  //共有者置換モードの内訳
  static $change_common_selector_list = array('' => 'なし', 'change_common', 'change_hermit_common');

  static $change_mad_enable          = true; //狂人置換村 (管理人カスタムモード)
  static $change_fanatic_mad_enable  = true; //狂信者村
  static $change_whisper_mad_enable  = true; //囁き狂人村
  static $change_immolate_mad_enable = true; //殉教者村
  //狂人置換モードの内訳
  static $change_mad_selector_list = array('' => 'なし', 'change_mad', 'change_fanatic_mad',
    'change_whisper_mad', 'change_immolate_mad');

  static $change_cupid_enable          = true; //キューピッド置換村 (管理人カスタムモード)
  static $change_mind_cupid_enable     = true; //女神村
  static $change_triangle_cupid_enable = true; //小悪魔村
  static $change_angel_enable          = true; //天使村
  //キューピッド置換モードの内訳
  static $change_cupid_selector_list = array('' => 'なし', 'change_cupid', 'change_mind_cupid',
    'change_triangle_cupid', 'change_angel');

  //-- 特殊配役モード --//
  static $chaos_enable       = true; //闇鍋モード
  static $chaosfull_enable   = true; //真・闇鍋モード
  static $chaos_hyper_enable = true; //超・闇鍋モード
  static $chaos_verso_enable = true; //裏・闇鍋モード
  static $duel               = true; //決闘村
  static $gray_random_enable = true; //グレラン村
  static $quiz_enable        = true; //クイズ村
  //特殊配役モードの内訳
  static $special_role_list = array('' => 'なし', 'chaos', 'chaosfull', 'chaos_hyper',
    'chaos_verso', 'duel', 'gray_random', 'quiz');

  //-- 闇鍋モード専用設定 --//
  static $topping_enable = true; //固定配役追加モード
  static $topping_list = array(
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
    'o' => 'O：陰陽村');

  static $boost_rate_enable = true; //出現率変動モード
  static $boost_rate_list = array(
    ''  => 'なし',
    'a' => 'A：新顔村',
    'b' => 'B：平等村',
    'c' => 'C：派生村',
    'd' => 'D：封蘇村',
    'e' => 'E：封憑村',
    'f' => 'F：合戦村',
    'g' => 'G：独身村',
    'h' => 'H：無毒村',);

  //配役通知設定
  static $chaos_open_cast_enable      = true; //配役内訳を表示する
  static $chaos_open_cast_camp_enable = true; //陣営通知
  static $chaos_open_cast_role_enable = true; //役職通知
  //通知モードのデフォルト ['':無し / 'camp':陣営 / 'role':役職 / 'full':完全]
  static $default_chaos_open_cast = 'camp'; //陣営通知

  //サブ役職制限
  static $sub_role_limit_enable             = true; //サブ役職制限
  static $sub_role_limit_easy_enable        = true; //サブ役職制限：EASYモード
  static $sub_role_limit_normal_enable      = true; //サブ役職制限：NORMALモード
  static $sub_role_limit_hard_enable        = true; //サブ役職制限：HARDモード
  static $sub_role_limit_no_sub_role_enable = true; //サブ役職をつけない
  //サブ役職制限のデフォルト
  //['':制限無し / 'no_sub':サブ役職をつけない / 'easy':EASYモード / 'normal':NORMALモード / 'hard':HARDモード]
  static $default_sub_role_limit = 'no_sub_role'; //つけない

  //その他
  static $secret_sub_role_enable  = true; //サブ役職を本人に通知しない
  static $default_secret_sub_role = false;
}
