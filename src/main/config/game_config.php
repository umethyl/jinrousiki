<?php
//-- 村メンテナンス・作成設定 --//
class RoomConfig{
  //-- 村メンテナンス設定 --//
  //村内の最後の発言から廃村になるまでの時間 (秒) (あまり短くすると沈黙等と競合する可能性あり)
  var $die_room = 1200;

  //最大並列プレイ可能村数
  var $max_active_room = 4;

  //次の村を立てられるまでの待ち時間 (秒)
  var $establish_wait = 120;

  //終了した村のユーザのセッション ID データをクリアするまでの時間 (秒)
  //(この時間内であれば、過去ログページに再入村のリンクが出現します)
  var $clear_session_id = 86400; //24時間

  //村立て・入村制限
  /* IP アドレスは strpos() による先頭一致、ホスト名は正規表現 */
  var $white_list_ip = array(); //IP アドレス (ホワイトリスト)
  var $black_list_ip = array(); //IP アドレス (ブラックリスト)
  var $white_list_host = NULL; //ホスト名 (ホワイトリスト)
  var $black_list_host = NULL; //ホスト名 (ブラックリスト)
  //var $black_list_host = '/localhost.localdomain/'; //入力例

  //-- 村作成設定 --//
  var $room_name          = 90; //村名の最大文字数 (byte)
  var $room_name_input    = 50; //村名の入力欄サイズ (文字数)
  var $room_comment       = 90; //村の説明の最大文字数 (byte)
  var $room_comment_input = 50; //村の説明の入力欄サイズ (文字数)
  var $gm_password        = 50; //GM ログインパスワードの最大文字数 (byte)
  var $gm_password_input  = 20; //GM ログインパスワードの入力欄サイズ
  var $ng_word = '/http:\/\//i'; //入力禁止文字列 (正規表現)

  //最大人数のリスト
  var $max_user_list = array(8, 11, 16, 22, 32, 50);
  var $default_max_user = 22; //デフォルトの最大人数 ($max_user_list にある値を入れること)

  //各オプションを有効に [true:する / false:しない]
  //デフォルトでチェックを [true:つける / false:つけない]
  var $wish_role = true; //役割希望制
  var $default_wish_role = false;

  var $real_time = true; //リアルタイム制 (初期設定は TimeConfig->default_day/night 参照)
  var $default_real_time = true;

  var $wait_morning = true; //早朝待機制 (待機時間設定は TimeConfig->wait_morning 参照)
  var $default_wait_morning = false;

  var $open_vote = true; //投票した票数を公表する
  var $default_open_vote = false;

  var $open_day = true; //オープニングあり
  var $default_open_day = false;

  var $dummy_boy = true; //初日の夜は身代わり君
  var $default_dummy_boy = true;

  var $gerd = true; //ゲルト君モード
  var $default_gerd = false;

  var $not_open_cast  = true; //霊界で配役を公開しない
  var $auto_open_cast = true; //霊界で配役を自動で公開する

  //霊界オフモードのデフォルト [NULL:無し / 'auto':自動オフ / 'full': 完全オフ ]
  var $default_not_open_cast = 'auto';

  var $poison = true; //埋毒者出現 (必要人数は CastConfig->poison 参照)
  var $default_poison = true;

  var $assassin = true; //暗殺者出現 (必要人数は CastConfig->assassin 参照)
  var $default_assassin = false;

  var $boss_wolf = true; //白狼出現 (必要人数は CastConfig->boss_wolf 参照)
  var $default_boss_wolf = false;

  var $poison_wolf = true; //毒狼出現 (必要人数は CastConfig->poison_wolf 参照)
  var $default_poison_wolf = false;

  var $possessed_wolf = true; //憑狼出現 (必要人数は CastConfig->possessed_wolf 参照)
  var $default_possessed_wolf = false;

  var $sirius_wolf = true; //天狼出現 (必要人数は CastConfig->sirius_wolf 参照)
  var $default_sirius_wolf = false;

  var $cupid = true; //キューピッド出現 (必要人数は CastConfig->cupid 参照)
  var $default_cupid = false;

  var $medium = true; //巫女出現 (必要人数は CastConfig->medium 参照)
  var $default_medium = false;

  var $mania = true; //神話マニア出現 (必要人数は CastConfig->mania 参照)
  var $default_mania = false;

  var $decide = true; //決定者出現 (必要人数は CastConfig->decide 参照)
  var $default_decide = true;

  var $authority = true; //権力者出現 (必要人数は CastConfig->authority 参照)
  var $default_authority = true;

  var $liar = true; //狼少年村
  var $default_liar = false;

  var $gentleman = true; //紳士・淑女村
  var $default_gentleman = false;

  var $sudden_death = true; //虚弱体質村
  var $default_sudden_death = false;

  var $perverseness = true; //天邪鬼村
  var $default_perverseness = false;

  var $deep_sleep = true; //静寂村
  var $default_deep_sleep = false;

  var $mind_open = true; //白夜村
  var $default_mind_open = false;

  var $blinder = true; //宵闇村
  var $default_blinder = false;

  var $critical = true; //急所村
  var $default_critical = false;

  var $joker = true; //ババ抜き村
  var $default_joker = false;

  var $detective = true; //探偵村
  var $default_detective = false;

  var $festival = true; //お祭り村
  var $default_festival = false;

  var $replace_human = true; //村人置換村
  var $full_mania = true; //神話マニア村
  var $full_chiroptera = true; //蝙蝠村
  var $full_cupid = true; //キューピッド村
  //置換モードの内訳 (replace_human：管理人カスタムモード)
  var $replace_human_list = array('full_mania', 'full_chiroptera', 'full_cupid', 'replace_human');

  //特殊配役村の内訳
  var $special_role_list = array('chaos', 'chaosfull', 'chaos_hyper', 'duel', 'gray_random', 'quiz');

  var $chaos = true; //闇鍋モード
  var $chaosfull = true; //真・闇鍋モード
  var $chaos_hyper = true; //超・闇鍋モード

  var $topping = true; //固定配役追加モード
  //GameOptionImage->topping_* @ message_config.php / CastConfig->topping_list と対応させる
  var $topping_list = array('a', 'b', 'c', 'd', 'e', 'f'); //配役タイプ

  var $chaos_open_cast = true; //配役内訳を表示する (闇鍋モード専用オプション)
  var $chaos_open_cast_camp = true; //陣営毎の総数を表示する (闇鍋モード専用オプション)
  var $chaos_open_cast_role = true; //役職の種類毎の総数を表示する (闇鍋モード専用オプション)

  //通知モードのデフォルト [NULL:無し / 'camp':陣営 / 'role':役職 / 'full':完全]
  var $default_chaos_open_cast = 'camp'; //陣営通知

  var $secret_sub_role = true; //サブ役職を本人に通知しない (闇鍋モード専用オプション)
  var $default_secret_sub_role = false;

  var $sub_role_limit = true; //サブ役職制限 (闇鍋モード専用オプション)
  var $sub_role_limit_easy   = true; //サブ役職制限：EASYモード
  var $sub_role_limit_normal = true; //サブ役職制限：NORMALモード
  var $no_sub_role = true; //サブ役職をつけない
  //サブ役職制限のデフォルト [NULL:制限無し / no:つけない / easy:EASYモード / normal:NORMALモード]
  var $default_sub_role_limit = 'no'; //つけない (no_sub_role)

  var $duel = true; //決闘村
  var $gray_random = true; //グレラン村
  var $quiz = true; //クイズ村
}

//-- ゲーム設定 --//
class GameConfig{
  //-- 住人登録 --//
  //入村制限 (同じ村に同じ IP で複数登録) (true：許可しない / false：許可する)
  var $entry_one_ip_address = true;

  //トリップ対応 (true：変換する / false： "#" が含まれていたらエラーを返す)
  var $trip = true;
  var $trip_2ch = true; //2ch 互換 (12桁対応) モード (true：有効 / false：無効)

  //文字数制限 (byte)
  var $entry_uname_limit = 50; //ユーザ名と村人の名前
  var $entry_profile_limit = 300; //プロフィール
  var $say_limit = 2048; //村の発言
  var $say_line_limit = 20; //村の発言 (行数)

  //-- 表示設定 --//
  var $quote_words = false; //発言を「」で括る
  var $replace_talk = false; //発言置換モード：発言内容の一部を強制置換する
  var $replace_talk_list = array(); //発言置換モードの変換リスト
  //var $replace_talk_list = array('◆' => '◇'); //流石ツール対応例
  var $display_talk_limit = 500; //ゲーム開始前後の発言表示数の限界値

  //-- 投票 --//
  var $self_kick = true; //自分への KICK (true：有効 / false：無効)
  var $kick = 3; //KICK 処理を実施するのに必要な投票数
  var $draw = 5; //引き分け処理を実施する再投票回数 (「再投票」なので実際の投票回数は +1 される)

  //-- 役職の能力設定 --//
  //毒能力者を処刑した際に巻き込まれる対象 (true:投票者ランダム / false:完全ランダム)
  var $poison_only_voter = false;

  //狼が毒能力者を襲撃した際に巻き込まれる対象 (true:投票者固定 / false:ランダム)
  var $poison_only_eater = true;

  var $cupid_self_shoot = 18; //キューピッドが他人撃ち可能となる参加人数
  var $cute_wolf_rate =  1; //萌狼の発動率 (%)
  var $gentleman_rate = 13; //紳士・淑女の発動率 (%)
  var $liar_rate      = 95; //狼少年の発動率 (%)

  //狼少年の変換テーブル
  var $liar_replace_list = array('村人' => '人狼', '人狼' => '村人',
				 'むらびと' => 'おおかみ', 'おおかみ' => 'むらびと',
				 'ムラビト' => 'オオカミ', 'オオカミ' => 'ムラビト',
				 '本当' => '嘘', '嘘' => '本当',
				 '真' => '偽', '偽' => '真',
				 '人' => '狼', '狼' => '人',
				 '白' => '黒', '黒' => '白',
				 '○' => '●', '●' => '○',
				 'CO' => '潜伏', 'ＣＯ' => '潜伏', '潜伏' => 'CO',
				 '吊り' => '噛み', '噛み' => '吊り',
				 'グレラン' => 'ローラー', 'ローラー'  => 'グレラン',
				 '少年' => '少女', '少女' => '少年',
				 'しょうねん' => 'しょうじょ', 'しょうじょ' => 'しょうねん',
				 'おはよう' => 'おやすみ', 'おやすみ' => 'おはよう'
				 );

  //虹色迷彩の変換テーブル
  var $rainbow_replace_list = array('赤' => '橙', '橙' => '黄', '黄' => '緑', '緑' => '青',
				    '青' => '藍', '藍' => '紫', '紫' => '赤');

  //七曜迷彩の変換テーブル
  var $weekly_replace_list = array('月' => '火', '火' => '水', '水' => '木', '木' => '金',
				   '金' => '土', '土' => '日', '日' => '月');

  //恋色迷彩の変換テーブル
  var $passion_replace_list = array('村人' => '好き', '好き' => '村人',
				    '人狼' => '嫌い', '嫌い' => '人狼',
				    'むらびと' => 'すき', 'すき' => 'むらびと',
				    'おおかみ' => 'きらい', 'きらい' => 'おおかみ',
				    'ムラビト' => 'スキ', 'スキ' => 'ムラビト',
				    'オオカミ' => 'キライ', 'キライ' => 'オオカミ',
				    '白' => '愛してる', '愛してる' => '白',
				    '黒' => '妬ましい', '妬ましい' => '黒',
				    '○' => 'あいしてる', 'あいしてる' => '○',
				    '●' => 'ねたましい', 'ねたましい' => '●',
				    'グレラン' => '告白', '告白'  => 'グレラン',
				    'ローラー' => 'ハーレム', 'ハーレム'  => 'ローラー',
				    );

  //役者の変換テーブル
  var $actor_replace_list = array('です' => 'みょん');

  var $invisible_rate = 10; //光学迷彩の発言が空白に入れ替わる確率 (%)
  var $silent_length  = 25; //無口が発言できる最大文字数

  //-- 「異議」あり --//
  var $objection = 5; //使用可能回数
  var $objection_image = 'img/objection.gif'; //「異議」ありボタンの画像パス

  //-- 自動更新 --//
  var $auto_reload = true; //game_view.php で自動更新を有効にする / しない (サーバ負荷に注意)
  var $auto_reload_list = array(15, 30, 45, 60, 90, 120); //自動更新モードの更新間隔 (秒) のリスト

  //-- その他 --//
  var $power_gm = false; //強権 GM モード (ON：true / OFF：false)
  var $random_message = false; //ランダムメッセージの挿入 (する：true / しない：false)
}

//ゲームの時間設定
class TimeConfig{
  //投票待ち超過時間 (秒) (この時間を過ぎても未投票の人がいたら突然死処理されます)
  var $sudden_death = 180;

  //サーバダウン判定時間 (秒)
  //超過のマイナス時間がこの閾値を越えた場合はサーバが一時的にダウンしていたと判定して、
  //超過時間をリセットします
  var $server_disconnect = 90;

  //警告音開始 (秒) (超過の残り時間がこの時間を切っても未投票の人がいたら警告音が鳴ります)
  var $alert = 90;

  //警告音感覚 (秒) (警告音の鳴る間隔)
  var $alert_distance = 6;

  //-- リアルタイム制 --//
  var $default_day   = 5; //デフォルトの昼の制限時間(分)
  var $default_night = 3; //デフォルトの夜の制限時間(分)

  //-- 会話を用いた仮想時間制 --//
  //昼の制限時間 (昼は12時間、spend_time=1(半角100文字以内) で 12時間 ÷ $day 進みます)
  var $day = 96;

  //夜の制限時間 (夜は 6時間、spend_time=1(半角100文字以内) で  6時間 ÷ $night 進みます)
  var $night = 24;

  //非リアルタイム制でこの閾値を過ぎると沈黙となり、設定した時間が進みます(秒)
  var $silence = 60;

  //沈黙経過時間 (12時間 ÷ $day(昼) or 6時間 ÷ $night (夜) の $silence_pass 倍の時間が進みます)
  var $silence_pass = 8;

  var $wait_morning  = 15; //早朝待機制の待機時間(秒)
}

//-- 村のオプション画像 --//
class RoomImage extends ImageManager{
  /*
    max[NN].gif という画像が該当パス内にあった場合は村の最大参加人数の表示に使用される。
    例) max8.gif (8人村用)
  */
  var $path      = 'room_option';
  var $extension = 'gif';
  var $class     = 'option';
}

//-- 役職の画像 --//
class RoleImage extends ImageManager{
  var $path      = 'role';
  var $extension = 'gif';
  var $class     = '';
}

//-- 勝利陣営の画像 --//
class VictoryImage extends VictoryImageBase{
  var $path      = 'victory_role';
  var $extension = 'gif';
  var $class     = 'winner';
}

//ゲームプレイ時のアイコン表示設定
class IconConfig extends IconConfigBase{
  var $path   = 'user_icon'; //ユーザアイコンのパス
  var $dead   = 'grave.jpg'; //死者
  var $wolf   = 'wolf.gif';  //狼
  var $width  = 45; //表示サイズ(幅)
  var $height = 45; //表示サイズ(高さ)
  var $view   = 100; //一画面に表示するアイコンの数
  var $page   = 10; //一画面に表示するページ数の数

  function IconConfig(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }
}

//-- 音源設定 --//
class Sound extends SoundBase{
  var $path      = 'swf'; //音源のパス
  var $extension = 'swf'; //拡張子

  var $entry            = 'sound_entry';            //入村
  var $full             = 'sound_full';             //定員
  var $morning          = 'sound_morning';          //夜明け
  var $revote           = 'sound_revote';           //再投票
  var $novote           = 'sound_novote';           //未投票告知
  var $alert            = 'sound_alert';            //未投票警告
  var $objection_male   = 'sound_objection_male';   //異議あり(男)
  var $objection_female = 'sound_objection_female'; //異議あり(女)
}

//過去ログ表示設定
class OldLogConfig{
  var $view = 20; //一画面に表示する村の数
  var $page =  5; //一画面に表示するページ数の数
  var $reverse = true; //デフォルトの村番号の表示順 (true:逆にする / false:しない)
}
