<?php
//-- データベース設定 --//
class DatabaseConfig extends DatabaseConfigBase{
  //データベースサーバのホスト名 hostname:port
  //ポート番号を省略するとデフォルトポートがセットされます。(MySQL:3306)
  var $host = 'localhost';

  //データベースのユーザ名
  var $user = 'xxxx';

  //データベースサーバのパスワード
  var $password = 'xxxxxxxx';

  //データベース名
  var $name = 'jinrou';

  //サブデータベースのリスト (サーバによってはサブのデータベースを作れないので注意)
  /*
    過去ログ表示専用です。old_log.php の引数に db_no=[数字] を追加すると
    設定したサブのデータベースに切り替えることができます。
    例) $name_list = array('log_a', 'log_b');
        old_log.php?db_no=2 => log_b のデータベースのログを表示
  */
  var $name_list = array();

  //文字コード
  var $encode = 'utf8';
}

//-- サーバ設定 --//
class ServerConfig{
  //サーバのURL
  var $site_root = 'http://localhost/jinrou/';

  //タイトル
  var $title = '汝は人狼なりや？';

  //サーバのコメント
  var $comment = '';

  //管理者 (任意)
  var $admin = '';

  //サーバの文字コード
  /*
    変更する場合は全てのファイル自体の文字コードを自前で変更してください
    include/init.php も参照してください
  */
  var $encode = 'UTF-8';

  //戻り先のページ
  var $back_page = '';

  //管理者用パスワード
  var $system_password = 'xxxxxxxx';

  //パスワード暗号化用 salt
  var $salt = 'xxxx';

  //村作成パスワード (NULL 以外を設定しておくと村作成画面にパスワード入力欄が表示されます)
  var $room_password = NULL;

  //村立てテストモード (村立ての DB アクセス処理をスキップします。開発者テスト用スイッチです)
  var $dry_run_mode = false;

  //村作成禁止 (true にすると村の作成画面が表示されず、村を作成できなくなります)
  var $disable_establish = false;

  //村メンテナンス停止 (true にすると村の自動廃村処理などが実行されなくなります)
  var $disable_maintenance = false;

  //村情報非表示モード (村立てテストなどの開発者テスト用スイッチです)
  var $secret_room = false;

  //タイムゾーンが設定できない場合に時差を秒単位で設定するか否か
  var $adjust_time_difference = false;

  //$adjust_time_difference が有効な時の時差 (秒数)
  var $offset_seconds = 32400; //9時間

  //更新前のスクリプトのリビジョン番号
  /*
    ※ この機能は Ver. 1.4.0 beta1 (revision 152) で実装されました。

    更新前のスクリプトの class ScriptInfo (config/version.php) で
    定義されている $revision を設定することで admin/setup.php で
    行われる処理が最適化されます。

    初めて当スクリプトを設置する場合や、データベースを一度完全消去して
    再設置する場合は 0 を設定して下さい。

    更新前のスクリプトに該当ファイルや変数がない場合や、
    バージョンが分からない場合は 1 を設定してください。

    更新後のリビジョン番号と同じか、それより大きな値を設定すると
    admin/setup.php の処理は常時スキップされます。
  */
  var $last_updated_revision = 1;
}

//-- 村情報共有サーバの設定 --//
class SharedServerConfig extends ExternalLinkBuilder{
  var $disable = true; //無効設定 <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト
  var $server_list = array(
     /* 設定例
    'cirno' => array('name' => 'チルノ鯖',
		     'url' => 'http://www12.atpages.jp/cirno/',
		     'encode' => 'UTF-8',
		     'separator' => '<!-- atpages banner tag -->',
		     'footer' => '</a><br>',
		     'disable' => false),
     */
			   );
}

//アイコン登録設定
class UserIcon extends UserIconBase{
  var $disable_upload = false; //アイコンのアップロードの停止設定 (true:停止する / false:しない)
  var $name   = 30;    //アイコン名につけられる文字数(半角)
  var $size   = 15360; //アップロードできるアイコンファイルの最大容量(単位：バイト)
  var $width  = 45;    //アップロードできるアイコンの最大幅
  var $height = 45;    //アップロードできるアイコンの最大高さ
  var $number = 1000;  //登録できるアイコンの最大数
  var $column = 4;     //一行に表示する個数
  var $gerd   = 0;     //ゲルト君モード用のアイコン番号
  var $password = 'xxxx'; //アイコン編集パスワード
  var $cation = ''; //注意事項 (空なら何も表示しない)
}

//メニューリンク表示設定
class MenuLinkConfig extends MenuLinkConfigBase{
  var $list = array();
  /* 設定例
  var $list = array('SourceForge' => 'http://sourceforge.jp/projects/jinrousiki/',
		    '開発・バグ報告スレ' =>
		    'http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1240771280/l50',
		    '新役職提案スレ' =>
		    'http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/l50'
		    );
  */

  var $add_list = array(
    /* 設定例
    '式神研系' => array('チルノ鯖' => 'http://www12.atpages.jp/cirno/',
			'Eva 鯖' => 'http://jinrou.kuroienogu.net/',
			'SourceForge' => 'http://sourceforge.jp/projects/jinrousiki/',
			'開発・バグ報告スレ' => 'http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1240771280/l50',
			'新役職提案スレ' => 'http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/l50')
    */
			);
}

//告知スレッド表示設定
class BBSConfig extends BBSConfigBase{
  var $disable = true; //表示無効設定 (true:無効にする / false:しない)
  var $title = '告知スレッド情報'; //表示名
  var $raw_url = 'http://jbbs.livedoor.jp/bbs/rawmode.cgi'; //データ取得用 URL
  var $view_url = 'http://jbbs.livedoor.jp/bbs/read.cgi'; //表示用 URL
  var $thread = '/game/43883/1260623018/'; //スレッドのアドレス (例)
  var $encode = 'EUC-JP'; //スレッドの文字コード
  var $size = 5; //表示するレスの数
}

//素材情報設定
class CopyrightConfig extends CopyrightConfigBase{
  //システム標準情報
  var $list = array('システム' =>
		    array('PHP4 + MYSQLスクリプト' => 'http://f45.aaa.livedoor.jp/~netfilms/',
			  'mbstringエミュレータ' => 'http://sourceforge.jp/projects/mbemulator/',
			  'Twitter投稿モジュール' => 'https://github.com/abraham/twitteroauth'
			  ),
		    '写真素材' =>
		    array('天の欠片' => 'http://keppen.web.infoseek.co.jp/'),
		    'フォント素材' =>
		    array('あずきフォント' => 'http://azukifont.mints.ne.jp/')
		    );

  //追加情報
  var $add_list = array('写真素材' =>
			array('Le moineau - すずめのおやど -' => 'http://moineau.fc2web.com/'),
			);
}

//-- 開発用ソースアップロード設定 --//
class SourceUploadConfig{
  var $disable = true; //無効設定 <アップロードを [true:無効 / false:有効] にする>

  //ソースアップロードフォームのパスワード
  var $password = 'upload';

  //フォームの最大文字数と表示名
  var $form_list = array('name'     => array('size' => 20, 'label' => 'ファイル名'),
			 'caption'  => array('size' => 80, 'label' => 'ファイルの説明'),
			 'user'     => array('size' => 20, 'label' => '作成者名'),
			 'password' => array('size' => 20, 'label' => 'パスワード'));

  //最大ファイルサイズ (バイト)
  var $max_size = 10485760; //10 Mbyte
}

//-- Twitter 投稿設定 --//
class TwitterConfig extends TwitterConfigBase{
  var $disable = true; //Twitter 投稿停止設定 (true:停止する / false:しない)
  var $server = 'localhost'; //サーバ名
  var $hash = ''; //ハッシュタグ (任意、「#」は不要)
  var $add_url    = false; //サーバの URL 追加設定 (true:追加する/false:しない)
  var $direct_url = false; //村への直リンク追加設定 (要：$add_url:true / true: 追加する/false しない)
  var $short_url  = false; //TinyURL を用いた URL 短縮処理設定 (true:行う / false:行わない)
  var $key_ck = 'xxxx'; //Consumer key
  var $key_cs = 'xxxx'; //Consumer secret
  var $key_at = 'xxxx'; //Access Token
  var $key_as = 'xxxx'; //Access Token Secret

  //-- 関数 --//
  //メッセージのセット
  function GenerateMessage($id, $name, $comment){
    return "【{$this->server}】{$id}番地に{$name}村\n～{$comment}～ が建ちました";
  }
}
