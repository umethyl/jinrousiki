<?php
//-- 村メンテナンス・作成設定 --//
class RoomConfig {
  /* 村メンテナンス設定 */
  //村内の最後の発言から廃村になるまでの時間 (秒) (あまり短くすると沈黙等と競合する可能性あり)
  const DIE_ROOM = 1200;

  //終了した村のユーザのセッション ID データの保持期間 (秒)
  //(この時間内であれば、過去ログページに再入村のリンクが出現します)
  const KEEP_SESSION = 86400; //24時間

  /* 村立て・入村制限 */
  //IP アドレスは strpos() による先頭一致、ホスト名は正規表現
  public static $white_list_ip = []; //IP アドレス (ホワイトリスト)
  public static $black_list_ip = []; //IP アドレス (ブラックリスト)
  public static $white_list_host = null; //ホスト名 (ホワイトリスト)
  public static $black_list_host = null; //ホスト名 (ブラックリスト)
  //public static $black_list_host = '/localhost.localdomain/'; //入力例
  public static $white_list_trip = []; //トリップ (ホワイトリスト/入村制限限定)
  //public static $white_list_trip = ['◆1234567']; //入力例

  /* 村立てのみ制限 */
  //記法は村立て・入村制限と同じ
  public static $establish_white_list_ip = []; //IP アドレス (ホワイトリスト)
  public static $establish_black_list_ip = []; //IP アドレス (ブラックリスト)
  public static $establish_white_list_host = null; //ホスト名 (ホワイトリスト)
  public static $establish_black_list_host = null; //ホスト名 (ブラックリスト)

  /* 村作成設定 */
  //最大人数のリスト
  public static $max_user_list = [8, 11, 16, 22, 32, 50];
  public static $default_max_user = 22; //デフォルトの最大人数 ($max_user_list にある値を入れること)

  public static $room_name          = 90; //村名の最大文字数 (byte)
  public static $room_name_input    = 50; //村名の入力欄サイズ (文字数)
  public static $room_comment       = 90; //村の説明の最大文字数 (byte)
  public static $room_comment_input = 50; //村の説明の入力欄サイズ (文字数)
  public static $gm_password        = 50; //GMログインパスワードの最大文字数 (byte)
  public static $gm_password_input  = 20; //GMログインパスワードの入力欄サイズ (文字数)

  const NG_WORD = '/http:\/\//i'; //入力禁止文字列 (正規表現)
  const ESTABLISH_WAIT  = 120; //次の村を立てられるまでの待ち時間 (秒)
  const MAX_ACTIVE_ROOM =   4; //最大並列プレイ可能村数
}
