<?php
require_once(dirname(__FILE__) . '/message_class.php'); //システムメッセージ格納クラス
require_once(dirname(__FILE__) . '/system_class.php');  //システム情報格納クラス

//部屋メンテナンス設定
class RoomConfig{
  //部屋最後の会話から廃村になるまでの時間 (秒)
  //(あまり短くすると沈黙等と競合する可能性あり)
  var $die_room = 1200;

  //終了した部屋のユーザのセッション ID データをクリアするまでの時間 (秒)
  var $clear_session_id = 1200;

  //最大人数のリスト (RoomImage->max_user_list と連動させる)
  var $max_user_list = array(8, 16, 22);

  //村作成パスワード (空なら判定スキップ)
  var $room_password = '';
}

//ゲーム設定
class GameConfig{
  //-- 住人登録 --//
  //入村制限 (同じ部屋に同じ IP で複数登録) (true：許可しない / false：許可する)
  var $entry_one_ip_address = true;

  //トリップ対応 (true：変換する / false： "#" が含まれていたらエラーを返す)
  //var $trip = true; //まだ実装されていません
  var $trip = false;

  //発言を「」で括る
  var $quote_words = false;

  //-- 投票 --//
  var $kick = 3; //何票で KICK 処理を行うか
  var $draw = 3; //再投票何回目で引き分けとするか

  //-- 役職 --//
  //配役テーブル
  /* 設定の見方
    [ゲーム参加人数] => array([配役名1] => [配役名1の人数], [配役名2] => [配役名2の人数], ...),
    ゲーム参加人数と配役名の人数の合計が合わない場合はゲーム開始投票時にエラーが返る
      human       : 村人
      wolf        : 人狼
      mage        : 占い師
      necromancer : 霊能者
      mad         : 狂人
      guard       : 狩人
      common      : 共有者
      fox         : 妖狐
      poison      : 埋毒者
      cupid       : キューピッド
  */
  var $role_list = array(
     4 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1),
     5 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1, 'poison' => 1),
     6 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1, 'poison' => 1, 'cupid' => 1),
     7 => array('human' =>  3, 'wolf' => 1, 'mage' => 1, 'guard' => 1, 'fox' => 1),
     8 => array('human' =>  5, 'wolf' => 2, 'mage' => 1),
     9 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1),
    10 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1),
    11 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1),
    12 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1),
    13 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common'=> 2),
    14 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2),
    15 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    16 => array('human' =>  6, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    17 => array('human' =>  7, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    18 => array('human' =>  8, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    19 => array('human' =>  9, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    20 => array('human' => 10, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    21 => array('human' => 11, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    22 => array('human' => 12, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1)
                         );

  //埋毒者を吊った際に巻き込まれる対象 (true:投票者ランダム / false:完全ランダム)
  var $poison_only_voter = false;

  //狼が埋毒者を噛んだ際に巻き込まれる対象 (true:投票者固定 / false:ランダム)
  var $poison_only_eater = true;

  var $cupid_self_shoot  = 10; //キューピッドが他人打ち可能となる最低村人数

  //-- 「異議」あり --//
  var $objection = 5; //最大回数
  var $objection_image = 'img/objection.gif'; //「異議」ありボタンの画像パス

  //-- 自動更新 --//
  var $auto_reload = true; //game_view.php で自動更新を有効にする / しない (サーバ負荷に注意)
  var $auto_reload_list = array(30, 45, 60); //自動更新モードの更新間隔(秒)のリスト
}

//ゲームの時間設定
class TimeConfig{
  //日没、夜明け残り時間ゼロでこの閾値を過ぎると投票していない人は突然死します(秒)
  var $sudden_death = 180;

  //-- リアルタイム制 --//
  var $default_day   = 5; //デフォルトの昼の制限時間(分)
  var $default_night = 3; //デフォルトの夜の制限時間(分)

  //-- 会話を用いた仮想時間制 --//
  //昼の制限時間(昼は12時間、spend_time=1(半角100文字以内) で 12時間 ÷ $day 進みます)
  var $day = 96;

  //夜の制限時間(夜は 6時間、spend_time=1(半角100文字以内) で  6時間 ÷ $night 進みます)
  var $night = 24;

  //非リアルタイム制でこの閾値を過ぎると沈黙となり、設定した時間が進みます(秒)
  var $silence = 60;

  //沈黙経過時間 (12時間 ÷ $day(昼) or 6時間 ÷ $night (夜) の $silence_pass 倍の時間が進みます)
  var $silence_pass = 8;
}

//ゲームプレイ時のアイコン表示設定
class IconConfig{
  var $path   = './user_icon';   //ユーザアイコンのパス
  var $width  = 45;              //表示サイズ(幅)
  var $height = 45;              //表示サイズ(高さ)
  var $dead   = 'img/grave.jpg'; //死者
  var $wolf   = 'img/wolf.gif';  //狼
}

//アイコン登録設定
class UserIcon{
  var $name   = 20;    //アイコン名につけられる文字数(半角)
  var $size   = 15360; //アップロードできるアイコンファイルの最大容量(単位：バイト)
  var $width  = 45;    //アップロードできるアイコンの最大幅
  var $height = 45;    //アップロードできるアイコンの最大高さ
  var $number = 1000;  //登録できるアイコンの最大数
}

//過去ログ表示設定
class OldLogConfig{
  var $one_page = 20;   //過去ログ一覧で1ページでいくつの村を表示するか
  var $reverse  = true; //デフォルトの村番号の表示順 (true:逆にする / false:しない)
}

//データ格納クラスをロード
$ROOM_CONF   = new RoomConfig();   //部屋メンテナンス設定
$GAME_CONF   = new GameConfig();   //ゲーム設定
$TIME_CONF   = new TimeConfig();   //ゲームの時間設定
$ICON_CONF   = new IconConfig();   //ユーザアイコン情報
$ROOM_IMG    = new RoomImage();    //村情報の画像パス
$ROLE_IMG    = new RoleImage();    //役職の画像パス
$VICTORY_IMG = new VictoryImage(); //勝利陣営の画像パス
$SOUND       = new Sound();        //音でお知らせ機能用音源パス
$MESSAGE     = new Message();      //システムメッセージ
?>
