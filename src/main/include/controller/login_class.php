<?php
//◆文字化け抑制◆//
//-- ログイン処理コントローラー --//
final class LoginController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'login';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function EnableCommand() {
    return RQ::Get()->login_manually;
  }

  protected static function RunCommand() {
    if (self::LoginManually()) {
      self::OutputResult(LoginMessage::MANUALLY_TITLE, LoginMessage::MANUALLY_BODY, 'game_frame');
    } else {
      $body = Text::Join(LoginMessage::FAILED_BODY, LoginMessage::FAILED_CAUTION);
      self::OutputResult(LoginMessage::FAILED_TITLE, $body);
    }
  }

  protected static function Output() {
    //自動(セッション) > 観戦ページジャンプ
    if (Session::Certify()) {
      self::OutputResult(LoginMessage::AUTO_TITLE, LoginMessage::AUTO_BODY, 'game_frame');
    } else {
      self::OutputResult(Message::VIEW_TITLE, Message::VIEW_BODY, 'game_view');
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

    //空判定 > ブラックリスト判定
    if ($uname == '' || $password == '') {
      return false;
    } elseif (false === ServerConfig::DEBUG_MODE && Security::IsLoginBlackList($trip)) {
      return false;
    }

    $crypt = Text::Crypt($password);
    //$crypt = $password; //デバッグ用

    return LoginDB::Execute($uname, $crypt);
  }

  //結果出力
  private static function OutputResult($title, $body, $jump = null) {
    if (is_null($jump)) {
      $url  = '';
    } else {
      $url  = URL::GetRoom($jump, RQ::Get()->room_no);
      $body = Text::Join($body, URL::GetJump($url));
    }
    HTML::OutputResult($title, $body, $url);
  }
}
