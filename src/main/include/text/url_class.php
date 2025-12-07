<?php
//-- URL 関連 --//
final class URL {
  const EXT       = '.php';
  const HEAD      = '?';
  const ADD       = '&';
  const DELIMITER = '/';
  const PAGE      = '#';

  /* 判定 */
  //存在判定 (db_no)
  public static function ExistsDB() {
    return is_int(RQ::Get(RequestDataGame::DB)) && RQ::Get(RequestDataGame::DB) > 0;
  }

  /* パラメータ取得 */
  //拡張子 + ヘッダー取得
  public static function GetExt() {
    return self::EXT . self::HEAD;
  }

  /* 変換 (URL) */
  //分割
  public static function Parse($url) {
    return Text::Parse($url, self::DELIMITER);
  }

  //結合 (URL)
  public static function Combine(...$list) {
    return ArrayFilter::Concat($list, self::DELIMITER);
  }

  /* リンク生成 */
  //取得 (部屋共通)
  public static function GetRoom($url, $id = null) {
    $value = (null === $id) ? DB::$ROOM->id : $id;
    return self::GenerateInt($url, RequestDataGame::ID, $value);
  }

  //取得 (ヘッダー/db_no)
  public static function GetHeaderDB($url) {
    return $url . self::EXT . self::GetDB(self::HEAD);
  }

  //取得 (移動用)
  public static function GetJump($url) {
    return sprintf(Message::JUMP, $url);
  }

  //取得 (自動更新)
  public static function GetReload($time) {
    return self::AddInt(RequestDataGame::RELOAD, $time);
  }

  //取得 (アイコン一覧)
  public static function GetIcon($url, $icon_no) {
    return $url . self::HEAD . self::ConvertInt('icon_no', $icon_no);
  }

  //取得 (検索リンク)
  public static function GetSearch($url, array $list) {
    $head = false;
    foreach ($list as $key => $value) {
      if (false === $head) {
	if (self::ExistsDB()) {
	  $str = self::GetHeaderDB($url) . self::AddString($key, $value);
	} else {
	  $str = $url . self::GetExt() . self::ConvertString($key, $value);
	}
	$head = true;
      } else {
	$str .= self::AddString($key, $value);
      }
    }
    return $str;
  }

  //取得 (新役職情報)
  public static function GetRole($role) {
    if (RoleDataManager::IsSub($role)) {
      $camp = 'sub_role';
    } else {
      $camp = RoleDataManager::GetCamp($role);
    }
    $page = ArrayFilter::Concat(['info', 'new_role', $camp], self::DELIMITER);
    return $page . self::EXT . self::PAGE . $role;
  }

  /* ヘッダーリンク生成 */
  //ヘッダーリンク生成 (数値型)
  public static function GenerateInt($url, $key, $value) {
    return $url . self::GetExt() . self::ConvertInt($key, $value);
  }

  //ヘッダーリンク生成 (bool 型)
  public static function GenerateSwitch($url, $key) {
    return $url . self::GetExt() . self::ConvertSwitch($key);
  }

  /* パラメータ加工 */
  //結合 (パラメータ)
  public static function Concat(array $list) {
    return ArrayFilter::Concat($list, self::ADD);
  }

  /* パラメータ生成 */
  //パラメータ生成 (数値型)
  public static function ConvertInt($key, $value) {
    return sprintf('%s=%d', $key, $value);
  }

  //パラメータ生成 (文字型)
  public static function ConvertString($key, $value) {
    return sprintf('%s=%s', $key, $value);
  }

  //パラメータ生成 (bool 型)
  public static function ConvertSwitch($key) {
    return self::ConvertString($key, Switcher::ON);
  }

  //パラメータ生成 (配列)
  public static function ConvertList($key, $value) {
    return sprintf('%s[]=%s', $key, $value);
  }

  //パラメータ追加 (数値型)
  public static function AddInt($key, $value) {
    return self::ADD . self::ConvertInt($key, $value);
  }

  //パラメータ追加 (文字型)
  public static function AddString($key, $value) {
    return self::ADD . self::ConvertString($key, $value);
  }

  //パラメータ追加 (bool 型)
  public static function AddSwitch($key) {
    return self::AddString($key, Switcher::ON);
  }

  //パラメータ追加 (db_no)
  public static function AddDB() {
    return self::GetDB(self::ADD);
  }

  //取得 (db_no)
  private static function GetDB($str) {
    if (self::ExistsDB()) {
      $key = RequestDataGame::DB;
      return $str . self::ConvertInt($key, RQ::Get($key));
    } else {
      return '';
    }
  }
}
