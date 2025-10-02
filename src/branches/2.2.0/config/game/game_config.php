<?php
//-- ゲーム設定 --//
class GameConfig {
  /* 住人登録 */
  const LIMIT_IP = true; //入村制限 (同じ村に同じ IP で複数登録) (true：許可しない / false：許可する)
  const TRIP     = true; //トリップ対応 (true：変換する / false： "#" が含まれていたらエラーを返す)
  const TRIP_2ch = true; //2ch 互換 (12桁対応) モード (true：有効 / false：無効)

  //文字数制限
  const LIMIT_UNAME   =  50;	//ユーザ名と村人の名前
  const LIMIT_PROFILE = 300;	//プロフィール

  /* 発言設定 */
  const LIMIT_TALK       =   500;	//ゲーム開始前後の発言表示数
  const LIMIT_SAY        = 20000;	//村の発言
  const LIMIT_SAY_LINE   =    50;	//村の発言 (行数)
  const LIMIT_LAST_WORDS = false; //遺言変更制限 (true：有効 / false：無効)
  const RANDOM_MESSAGE   = false; //ランダムメッセージの挿入 (する：true / しない：false)

  const QUOTE_TALK   = false; //発言を「」で括る
  const REPLACE_TALK = false; //発言置換モード：発言内容の一部を強制置換する
  static $replace_talk_list = array(); //発言置換モードの変換リスト
  //static $replace_talk_list = array('◆' => '◇'); //流石ツール対応例

  /* 表示設定 */
  //「異議」あり
  const OBJECTION = 5; //使用可能回数
  const OBJECTION_IMAGE = 'img/objection.gif'; //画像パス

  //自動更新
  const AUTO_RELOAD = true; //game_view.php で自動更新を有効にする / しない (サーバ負荷に注意)
  static $auto_reload_list = array(15, 30, 45, 60, 90, 120); //自動更新モードの更新間隔 (秒) のリスト

  /* 投票 */
  const SELF_KICK = true; //自分への KICK (true：有効 / false：無効)
  const KICK = 3; //KICK 処理を実施するのに必要な投票数
  const DRAW = 4; //引き分け処理を実施する再投票回数 (「再投票」なので実際の投票回数は +1 される)

  /* 役職の能力設定 */
  //毒能力者を処刑した際に巻き込まれる対象 (true:投票者ランダム / false:完全ランダム)
  const POISON_ONLY_VOTER = false;

  //狼が毒能力者を襲撃した際に巻き込まれる対象 (true:投票者固定 / false:ランダム)
  const POISON_ONLY_EATER = true;

  const CUPID_SELF_SHOOT = 18; //キューピッドが他人撃ち可能となる参加人数
  const CUTE_WOLF_RATE   =  1; //萌狼の発動率 (%)
  const GENTLEMAN_RATE   = 13; //紳士・淑女の発動率 (%)
  const LIAR_RATE        = 95; //狼少年の発動率 (%)
  const INVISIBLE_RATE   = 15; //光学迷彩の発言が空白に入れ替わる割合 (%)
  const SILENT_LENGTH    = 25; //無口が発言できる最大文字数
  //役者の変換テーブル
  static $actor_replace_list = array('です' => 'みょん');
  //恋色迷彩の変換テーブル
  static $passion_replace_list = array(
    '村人' => '好き', '好き' => '村人',
    '人狼' => '嫌い', '嫌い' => '人狼',
    'むらびと' => 'すき', 'すき' => 'むらびと',
    'おおかみ' => 'きらい', 'きらい' => 'おおかみ',
    'ムラビト' => 'スキ', 'スキ' => 'ムラビト',
    'オオカミ' => 'キライ', 'キライ' => 'オオカミ',
    '白' => '愛してる', '愛してる' => '白',
    '黒' => '妬ましい', '妬ましい' => '黒',
    '○' => 'あいしてる', 'あいしてる' => '○',
    '●' => 'ねたましい', 'ねたましい' => '●',
    'CO' => 'プロポーズ', 'ＣＯ' => 'プロポーズ', 'プロポーズ' => 'CO',
    'グレラン' => '告白', '告白'  => 'グレラン',
    'ローラー' => 'ハーレム', 'ハーレム'  => 'ローラー');

  /* その他 */
  const POWER_GM = false; //強権 GM モード (ON：true / OFF：false)

  //天候の出現比設定 (番号と天候の対応は WeatherData::$list 参照)
  static $weather_list = array(
     0 => 10,   1 => 20,   2 => 20,   3 => 20,   4 => 20,
     5 => 10,   6 => 15,   7 => 20,   8 => 20,   9 => 10,
    10 => 10,  11 => 30,  12 => 10,  13 => 20,  14 => 20,
    15 => 10,  16 => 20,  17 => 10,  18 => 15,  19 => 15,
    20 => 20,  21 => 20,  22 => 20,  23 => 20,  24 => 20,
    25 => 20,  26 => 20,  27 => 15,  28 => 25,  29 => 15,
    30 => 20,  31 => 20,  32 => 20,  33 => 15,  34 => 15,
    35 => 10,  36 => 20,  37 => 20,  38 => 20,  39 => 20,
    40 => 20,  41 => 10,  42 => 20,  43 => 20,  44 => 10,
    45 => 20,  46 => 20,  47 => 20,  48 => 15,  49 => 15,
    50 => 20,  51 => 20,  52 => 15,  53 => 15,  54 => 10,
    55 => 10,  56 => 10,  57 => 10,  58 => 10,  59 => 10);
}
