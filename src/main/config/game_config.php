<?php
/*
  変更履歴 from Ver. 1.5.0
  + GameConfig
    - 追加：$say_limit, $say_line_limit
*/

//-- 村メンテナンス・作成設定 --//
class RoomConfig{
  //-- 村メンテナンス設定 --//
  //村内の最後の発言から廃村になるまでの時間 (秒) (あまり短くすると沈黙等と競合する可能性あり)
  public $die_room = 1200;

  //最大並列プレイ可能村数
  public $max_active_room = 4;

  //次の村を立てられるまでの待ち時間 (秒)
  public $establish_wait = 120;

  //終了した村のユーザのセッション ID データをクリアするまでの時間 (秒)
  //(この時間内であれば、過去ログページに再入村のリンクが出現します)
  public $clear_session_id = 86400; //24時間

  //村立て・入村制限
  /* IP アドレスは strpos() による先頭一致、ホスト名は正規表現 */
  public $white_list_ip = array(); //IP アドレス (ホワイトリスト)
  public $black_list_ip = array(); //IP アドレス (ブラックリスト)
  public $white_list_host = null; //ホスト名 (ホワイトリスト)
  public $black_list_host = null; //ホスト名 (ブラックリスト)
  //public $black_list_host = '/localhost.localdomain/'; //入力例

  //-- 村作成設定 --//
  public $room_name          = 90; //村名の最大文字数 (byte)
  public $room_name_input    = 50; //村名の入力欄サイズ (文字数)
  public $room_comment       = 90; //村の説明の最大文字数 (byte)
  public $room_comment_input = 50; //村の説明の入力欄サイズ (文字数)
  public $gm_password        = 50; //GM ログインパスワードの最大文字数 (byte)
  public $gm_password_input  = 20; //GM ログインパスワードの入力欄サイズ
  public $ng_word = '/http:\/\//i'; //入力禁止文字列 (正規表現)

  //最大人数のリスト
  public $max_user_list = array(8, 11, 16, 22, 32, 50);
  public $default_max_user = 22; //デフォルトの最大人数 ($max_user_list にある値を入れること)

  //-- オプション出現設定 --//
  /* 例：役割希望制 (wish_role)
    オプション名 ($wish_role)    ：オプションを有効に   [true:する   / false:しない]
    初期設定 ($default_wish_role)：初期状態でチェックを [true:つける / false:つけない]
  */
  //-- 基本設定 --//
  public $wish_role = true; //役割希望制
  public $default_wish_role = false;

  public $real_time = true; //リアルタイム制 (初期設定は TimeConfig->default_day/night 参照)
  public $default_real_time = true;

  public $wait_morning = true; //早朝待機制 (待機時間設定は TimeConfig->wait_morning 参照)
  public $default_wait_morning = false;

  public $open_vote = true; //投票した票数を公表する
  public $default_open_vote = false;

  public $seal_message = true; //天啓封印
  public $default_seal_message = false;

  public $open_day = true; //オープニングあり
  public $default_open_day = false;

  //-- 身代わり君設定 --//
  public $dummy_boy = true; //初日の夜は身代わり君
  public $default_dummy_boy = true;

  public $gerd = true; //ゲルト君モード
  public $default_gerd = false;

  //-- 霊界公開設定 --//
  public $not_open_cast  = true; //霊界で配役を公開しない
  public $auto_open_cast = true; //霊界で配役を自動で公開する

  //霊界オフモードのデフォルト [null:無し / 'auto':自動オフ / 'full': 完全オフ ]
  public $default_not_open_cast = 'auto';

  //-- 追加役職設定 --//
  //必要人数は CastConfig の同名オプション名参照 (例： $poison => CastConfig->poison)
  public $poison = true; //埋毒者
  public $default_poison = true;

  public $assassin = true; //暗殺者
  public $default_assassin = false;

  public $wolf = true; //人狼追加
  public $default_wolf = false;

  public $boss_wolf = true; //白狼
  public $default_boss_wolf = false;

  public $poison_wolf = true; //毒狼
  public $default_poison_wolf = false;

  public $possessed_wolf = true; //憑狼
  public $default_possessed_wolf = false;

  public $sirius_wolf = true; //天狼
  public $default_sirius_wolf = false;

  public $fox = true; //妖狐追加
  public $default_fox = false;

  public $child_fox = true; //子狐
  public $default_child_fox = false;

  public $cupid = true; //キューピッド
  public $default_cupid = false;

  public $medium = true; //巫女
  public $default_medium = false;

  public $mania = true; //神話マニア
  public $default_mania = false;

  public $decide = true; //決定者
  public $default_decide = false;

  public $authority = true; //権力者
  public $default_authority = false;

  //-- 特殊村設定 --//
  public $detective = true; //探偵村
  public $default_detective = false;

  public $liar = true; //狼少年村
  public $default_liar = false;

  public $gentleman = true; //紳士・淑女村
  public $default_gentleman = false;

  public $deep_sleep = true; //静寂村
  public $default_deep_sleep = false;

  public $blinder = true; //宵闇村
  public $default_blinder = false;

  public $mind_open = true; //白夜村
  public $default_mind_open = false;

  public $critical = true; //急所村
  public $default_critical = false;

  public $sudden_death = true; //虚弱体質村
  public $default_sudden_death = false;

  public $perverseness = true; //天邪鬼村
  public $default_perverseness = false;

  public $joker = true; //ババ抜き村
  public $default_joker = false;

  public $death_note = true; //デスノート村
  public $default_death_note = false;

  public $weather = true; //天候あり
  public $default_weather = false;

  public $festival = true; //お祭り村
  public $default_festival = false;

  public $replace_human      = true; //村人置換村 (管理人カスタムモード)
  public $full_mad           = true; //狂人村
  public $full_cupid         = true; //キューピッド村
  public $full_quiz          = true; //出題者村
  public $full_vampire       = true; //吸血鬼村
  public $full_chiroptera    = true; //蝙蝠村
  public $full_mania         = true; //神話マニア村
  public $full_unknown_mania = true; //鵺村
  //村人置換モードの内訳
  public $replace_human_list = array(
    'replace_human', 'full_mad', 'full_cupid', 'full_quiz', 'full_vampire', 'full_chiroptera',
    'full_mania', 'full_unknown_mania');

  public $change_common        = true; //共有者置換村 (管理人カスタムモード)
  public $change_hermit_common = true; //隠者村
  //共有者置換モードの内訳
  public $change_common_list = array('change_common', 'change_hermit_common');

  public $change_mad          = true; //狂人置換村 (管理人カスタムモード)
  public $change_fanatic_mad  = true; //狂信者村
  public $change_whisper_mad  = true; //囁き狂人村
  public $change_immolate_mad = true; //殉教者村
  //狂人置換モードの内訳
  public $change_mad_list = array('change_mad', 'change_fanatic_mad', 'change_whisper_mad',
				  'change_immolate_mad');

  public $change_cupid          = true; //キューピッド置換村 (管理人カスタムモード)
  public $change_mind_cupid     = true; //女神村
  public $change_triangle_cupid = true; //小悪魔村
  public $change_angel          = true; //天使村
  //キューピッド置換モードの内訳
  public $change_cupid_list = array('change_cupid', 'change_mind_cupid', 'change_triangle_cupid',
				    'change_angel');

  //-- 特殊配役モード --//
  public $chaos       = true; //闇鍋モード
  public $chaosfull   = true; //真・闇鍋モード
  public $chaos_hyper = true; //超・闇鍋モード
  public $chaos_verso = true; //裏・闇鍋モード
  public $duel        = true; //決闘村
  public $gray_random = true; //グレラン村
  public $quiz        = true; //クイズ村
  //特殊配役モードの内訳
  public $special_role_list = array('chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso', 'duel',
				    'gray_random', 'quiz');

  //-- 闇鍋モード専用設定 --//
  public $topping = true; //固定配役追加モード
  //GameOptionImage->topping_* @ message_config.php / CastConfig->topping_list と対応させる
  public $topping_list = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l');

  public $boost_rate = true; //出現率変動モード
  //GameOptionImage->boost_rate_* @ message_config.php / CastConfig->boost_rate_list と対応させる
  public $boost_rate_list = array('a', 'b', 'c', 'd', 'e', 'f');

  //配役通知設定
  public $chaos_open_cast      = true; //配役内訳を表示する
  public $chaos_open_cast_camp = true; //陣営通知
  public $chaos_open_cast_role = true; //役職通知
  //通知モードのデフォルト [null:無し / 'camp':陣営 / 'role':役職 / 'full':完全]
  public $default_chaos_open_cast = 'camp'; //陣営通知

  //サブ役職制限
  public $sub_role_limit        = true; //サブ役職制限
  public $sub_role_limit_easy   = true; //サブ役職制限：EASYモード
  public $sub_role_limit_normal = true; //サブ役職制限：NORMALモード
  public $sub_role_limit_hard   = true; //サブ役職制限：HARDモード
  public $no_sub_role           = true; //サブ役職をつけない
  //サブ役職制限のデフォルト
  //[null:制限無し / no:つけない / easy:EASYモード / normal:NORMALモード / hard:HARDモード]
  public $default_sub_role_limit = 'no'; //つけない (no_sub_role)

  //その他
  public $secret_sub_role = true; //サブ役職を本人に通知しない
  public $default_secret_sub_role = false;
}

//-- ゲーム設定 --//
class GameConfig extends GameConfigBase{
  //-- 住人登録 --//
  //入村制限 (同じ村に同じ IP で複数登録) (true：許可しない / false：許可する)
  public $entry_one_ip_address = true;

  //トリップ対応 (true：変換する / false： "#" が含まれていたらエラーを返す)
  public $trip = true;
  public $trip_2ch = true; //2ch 互換 (12桁対応) モード (true：有効 / false：無効)

  //文字数制限 (byte)
  public $entry_uname_limit = 50; //ユーザ名と村人の名前
  public $entry_profile_limit = 300; //プロフィール
  public $say_limit = 2048; //村の発言
  public $say_line_limit = 20; //村の発言 (行数)

  //-- 表示設定 --//
  public $quote_words = false; //発言を「」で括る
  public $replace_talk = false; //発言置換モード：発言内容の一部を強制置換する
  public $replace_talk_list = array(); //発言置換モードの変換リスト
  //public $replace_talk_list = array('◆' => '◇'); //流石ツール対応例
  public $display_talk_limit = 500; //ゲーム開始前後の発言表示数の限界値

  //-- 投票 --//
  public $self_kick = true; //自分への KICK (true：有効 / false：無効)
  public $kick = 3; //KICK 処理を実施するのに必要な投票数
  public $draw = 4; //引き分け処理を実施する再投票回数 (「再投票」なので実際の投票回数は +1 される)

  //-- 役職の能力設定 --//
  //毒能力者を処刑した際に巻き込まれる対象 (true:投票者ランダム / false:完全ランダム)
  public $poison_only_voter = false;

  //狼が毒能力者を襲撃した際に巻き込まれる対象 (true:投票者固定 / false:ランダム)
  public $poison_only_eater = true;

  public $cupid_self_shoot = 18; //キューピッドが他人撃ち可能となる参加人数
  public $cute_wolf_rate   =  1; //萌狼の発動率 (%)
  public $gentleman_rate   = 13; //紳士・淑女の発動率 (%)
  public $liar_rate        = 95; //狼少年の発動率 (%)
  public $invisible_rate   = 15; //光学迷彩の発言が空白に入れ替わる割合 (%)
  public $silent_length    = 25; //無口が発言できる最大文字数
  //役者の変換テーブル
  public $actor_replace_list = array('です' => 'みょん');

  //-- 「異議」あり --//
  public $objection = 5; //使用可能回数
  public $objection_image = 'img/objection.gif'; //「異議」ありボタンの画像パス

  //-- 自動更新 --//
  public $auto_reload = true; //game_view.php で自動更新を有効にする / しない (サーバ負荷に注意)
  public $auto_reload_list = array(15, 30, 45, 60, 90, 120); //自動更新モードの更新間隔 (秒) のリスト

  //-- その他 --//
  public $power_gm = false; //強権 GM モード (ON：true / OFF：false)
  public $random_message = false; //ランダムメッセージの挿入 (する：true / しない：false)

  //天候の出現比設定 (番号と天候の対応は RoleData->weather_list 参照)
  public $weather_list = array(
     0 => 10,   1 => 20,   2 => 25,   3 => 20,   4 => 25,
     5 => 10,   6 => 15,   7 => 20,   8 => 20,   9 => 10,
    10 => 10,  11 => 30,  12 => 10,  13 => 20,  14 => 30,
    15 => 10,  16 => 20,  17 => 10,  18 => 15,  19 => 15,
    20 => 20,  21 => 20,  22 => 20,  23 => 20,  24 => 20,
    25 => 25,  26 => 25,  27 => 25,  28 => 25,  29 => 15,
    30 => 20,  31 => 20,  32 => 20,  33 => 15,  34 => 15,
    35 => 10,  36 => 20,  37 => 20,  38 => 30,  39 => 20,
    40 => 20,  41 => 10,  42 => 20,  43 => 20,  44 => 10,
    45 => 20,  46 => 20,  47 => 20,  48 => 15,  49 => 15,
    50 => 20,  51 => 20,  52 => 15,  53 => 15,  54 => 10);
}

//ゲームの時間設定
class TimeConfig{
  //投票待ち超過時間 (秒) (この時間を過ぎても未投票の人がいたら突然死処理されます)
  public $sudden_death = 120;
  #public $sudden_death = 180;

  //サーバダウン判定時間 (秒)
  //超過のマイナス時間がこの閾値を越えた場合はサーバが一時的にダウンしていたと判定して、
  //超過時間をリセットします
  public $server_disconnect = 90;

  //警告音開始 (秒) (超過の残り時間がこの時間を切っても未投票の人がいたら警告音が鳴ります)
  public $alert = 90;

  //警告音感覚 (秒) (警告音の鳴る間隔)
  public $alert_distance = 6;

  //-- リアルタイム制 --//
  public $default_day   = 5; //昼の制限時間の初期値 (分)
  public $default_night = 3; //夜の制限時間の初期値 (分)

  //-- 会話を用いた仮想時間制 --//
  //昼の制限時間 (昼は12時間、spend_time=1(半角100文字以内) で 12時間 ÷ $day 進みます)
  public $day = 96;

  //夜の制限時間 (夜は 6時間、spend_time=1(半角100文字以内) で  6時間 ÷ $night 進みます)
  public $night = 24;

  //非リアルタイム制でこの閾値を過ぎると沈黙となり、設定した時間が進みます(秒)
  public $silence = 60;

  //沈黙経過時間 (12時間 ÷ $day(昼) or 6時間 ÷ $night (夜) の $silence_pass 倍の時間が進みます)
  public $silence_pass = 8;

  public $wait_morning = 15; //早朝待機制の待機時間 (秒)
}

//-- 村のオプション画像 --//
class RoomImage extends ImageManager{
  /*
    max[NN].gif という画像が該当パス内にあった場合は村の最大参加人数の表示に使用される。
    例) max8.gif (8人村用)
  */
  public $path      = 'room_option';
  public $extension = 'gif';
  public $class     = 'option';
}

//-- 役職の画像 --//
class RoleImage extends ImageManager{
  public $path      = 'role';
  public $extension = 'gif';
  public $class     = '';
}

//-- 勝利陣営の画像 --//
class VictoryImage extends VictoryImageBase{
  public $path      = 'victory_role';
  public $extension = 'gif';
  public $class     = 'winner';
}

//ゲームプレイ時のアイコン表示設定
class IconConfig extends IconConfigBase{
  public $path   = 'user_icon'; //ユーザアイコンのパス
  public $dead   = 'grave.jpg'; //死者
  public $wolf   = 'wolf.gif';  //狼
  public $width  = 45; //表示サイズ(幅)
  public $height = 45; //表示サイズ(高さ)
  public $view   = 100; //一画面に表示するアイコンの数
  public $page   = 10; //一画面に表示するページ数の数

  function __construct(){ parent::__construct(); }
}

//-- 音源設定 --//
class Sound extends SoundBase{
  public $path      = 'swf'; //音源のパス
  public $extension = 'swf'; //拡張子

  public $entry            = 'sound_entry';            //入村
  public $full             = 'sound_full';             //定員
  public $morning          = 'sound_morning';          //夜明け
  public $revote           = 'sound_revote';           //再投票
  public $novote           = 'sound_novote';           //未投票告知
  public $alert            = 'sound_alert';            //未投票警告
  public $objection_male   = 'sound_objection_male';   //異議あり(男)
  public $objection_female = 'sound_objection_female'; //異議あり(女)
}

//過去ログ表示設定
class OldLogConfig{
  public $view = 20; //一画面に表示する村の数
  public $page =  5; //一画面に表示するページ数の数
  public $reverse = true; //デフォルトの村番号の表示順 (true:逆にする / false:しない)
}
