<?php
//-- ログイン処理クラス --//
class Login {
  //基幹処理
  static function Execute() {
    DB::Connect();
    if (RQ::Get()->login_manually) { //ユーザ名とパスワードで手動ログイン
      if (self::LoginManually()) {
	self::Output(LoginMessage::MANUALLY_TITLE, LoginMessage::MANUALLY_BODY, 'game_frame');
      } else {
	$body = LoginMessage::FAILED_BODY . Text::BRLF . LoginMessage::FAILED_CAUTION;
	self::Output(LoginMessage::FAILED_TITLE, $body);
      }
    }
    elseif (Session::Certify(false)) { //セッション ID から自動ログイン
      self::Output(LoginMessage::AUTO_TITLE, LoginMessage::AUTO_BODY, 'game_frame');
    }
    else { //単に呼ばれただけなら観戦ページに移動させる
      self::Output(Message::VIEW_TITLE, Message::VIEW_BODY, 'game_view');
    }
  }

  //手動ログイン
  /*
    セッションを失った場合、ユーザ名とパスワードでログインする
    返り値：bool (ログイン成否)
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

  //結果出力
  private static function Output($title, $body, $jump = null) {
    if (is_null($jump)) {
      $url = '';
    } else {
      $url = sprintf('%s.php?room_no=%s', $jump, RQ::Get()->room_no);
      $body .= Text::BRLF . sprintf(Message::JUMP, $url);
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
