<?php
//-- セキュリティ関連クラス --//
final class Security {
  /* 取得系 */
  //IPアドレス取得
  public static function GetIP() {
    return @$_SERVER['REMOTE_ADDR'];
  }

  //CSRF対策用トークン取得
  public static function GetToken($id) {
    return md5(ServerConfig::GAME_HASH . $id);
  }

  /* 検証系 */
  /**
   * 実行環境にダメージを与える可能性がある値が含まれているかどうか検査します。
   * @param  : mixed   : $data 検査対象の変数
   * @param  : boolean : $found 疑わしい値が存在しているかどうかを示す値。
                         この値がtrueの場合、強制的に詳細なスキャンが実行されます。
   * @return : boolean : 危険な値が発見された場合 true、それ以外の場合 false
   */
  public static function IsInvalidValue($data, $found = false) {
    $num = '22250738585072011';
    if (true === $found || Text::Search(str_replace('.', '', serialize($data)), $num)) {
      //文字列の中に問題の数字が埋め込まれているケースを排除する
      if (is_array($data)) {
	foreach ($data as $item) {
	  if (self::IsInvalidValue($item, true)) {
	    return true;
	  }
	}
      } else {
	$preg = '/^([0.]*2[0125738.]{15,16}1[0.]*)e(-[0-9]+)$/i';
	$item = strval($data);
	$matches = '';
	if (preg_match($preg, $item, $matches)) {
	  $exp = intval($matches[2]) + 1;
	  if (2.2250738585072011e-307 === floatval("{$matches[1]}e{$exp}")) {
	    return true;
	  }
	}
      }
    }
    return false;
  }

  //リファラ検証
  public static function IsInvalidReferer($page, $white_list = null) {
    if (is_array($white_list)) { //ホワイトリストチェック
      $addr = self::GetIP();
      foreach ($white_list as $host) {
	if (Text::IsPrefix($addr, $host)) {
	  return false;
	}
      }
    }
    $url = ServerConfig::SITE_ROOT . $page;
    return strncmp($_SERVER['HTTP_REFERER'] ?? '', $url, strlen($url)) != 0;
  }

  //CSRF対策用トークン検証
  public static function IsInvalidToken($id) {
    return RQ::Fetch()->token != self::GetToken($id);
  }

  /* 判定系 */
  //ブラックリスト判定 (ログイン用)
  public static function IsLoginBlackList($trip = '') {
    if (GameConfig::TRIP && $trip != '' && in_array($trip, RoomConfig::$white_list_trip)) {
      return false;
    }
    return self::IsBlackList();
  }

  //ブラックリスト判定 (村立て用)
  public static function IsEstablishBlackList() {
    return self::IsLoginBlackList() || self::IsBlackList('establish_');
  }

  //ブラックリスト判定
  private static function IsBlackList($prefix = '') {
    $addr = self::GetIP();
    $host = gethostbyaddr($addr);
    foreach (['white' => false, 'black' => true] as $type => $flag) {
      foreach (RoomConfig::${$prefix . $type . '_list_ip'} as $ip) {
	if (Text::IsPrefix($addr, $ip)) {
	  return $flag;
	}
      }
      $list = RoomConfig::${$prefix . $type . '_list_host'};
      if (isset($list) && preg_match($list, $host)) {
	return $flag;
      }
    }
    return false;
  }
}
