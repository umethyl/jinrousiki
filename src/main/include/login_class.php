<?php
//-- ログイン処理クラス --//
class Login {
  //基幹処理
  static function Execute() {
    DB::Connect();
    if (RQ::Get()->login_manually) { //ユーザ名とパスワードで手動ログイン
      if (self::LoginManually()) {
	self::Output('ログインしました', 'game_frame');
      }
      else {
	$str = 'ユーザ名とパスワードが一致しません。<br>' .
	  '(空白と改行コードは登録時に自動で削除されている事に注意してください)';
	self::Output('ログイン失敗', null, $str);
      }
    }

    if (Session::Certify(false)) { //セッション ID から自動ログイン
      self::Output('ログインしています', 'game_frame');
    } else { //単に呼ばれただけなら観戦ページに移動させる
      self::Output('観戦ページにジャンプ', 'game_view', '観戦ページに移動します');
    }
  }

  //手動ログイン処理
  /*
    セッションを失った場合、ユーザ名とパスワードでログインする
    ログイン成功/失敗を true/false で返す
  */
  private static function LoginManually() {
    extract(RQ::ToArray()); //引数を展開
    if (GameConfig::TRIP && $trip != '') {
      $trip = Text::Trip('#' . $trip); //トリップ変換
      $uname .= $trip;
    } else {
      $trip = ''; //ブラックリストチェック用にトリップを初期化
    }
    if ($uname == '' || $password == '') return false;

    //ブラックリストチェック
    if (! ServerConfig::DEBUG_MODE && Security::IsLoginBlackList($trip)) return false;

    $crypt = Text::Crypt($password);
    //$crypt = $password; //デバッグ用
    return LoginDB::Certify($uname, $crypt) && LoginDB::Update($uname, $crypt); //認証＆再登録処理
  }

  //結果出力関数
  private static function Output($title, $jump, $body = null) {
    if (is_null($body)) $body = $title;
    if (is_null($jump)) {
      $url = '';
    }
    else {
      $url = sprintf('%s.php?room_no=%s', $jump, RQ::Get()->room_no);
      $str = "。<br>\n".'切り替わらないなら <a href="%s" target="_top">ここ</a> 。';
      $body .= sprintf($str, $url);
    }
    HTML::OutputResult($title, $body, $url);
  }
}

//-- データベースアクセス (Login 拡張) --//
class LoginDB {
  //ユーザ認証
  static function Certify($uname, $password) {
    $query = <<<EOF
SELECT user_no FROM user_entry
WHERE room_no = ? AND uname = ? AND password = ? AND live <> ?
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, $uname, $password, 'kick'));
    return DB::Count() == 1;
  }

  //セッション ID 再登録
  static function Update($uname, $password) {
    $query = <<<EOF
UPDATE user_entry SET session_id = ?
WHERE room_no = ? AND uname = ? AND password = ? AND live <> ?
EOF;
    DB::Prepare($query, array(Session::GetID(true), RQ::Get()->room_no, $uname, $password, 'kick'));
    return DB::Execute();
  }
}
