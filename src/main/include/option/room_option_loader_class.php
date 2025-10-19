<?php
//-- 村作成用オプションローダー --//
class RoomOptionLoader {
  public static $stack       = [];
  public static $game_option = [];
  public static $role_option = [];
  public static $max_user    = 0;

  //オプション情報ロード
  public static function Load(array $list = []) {
    if (count($list) < 1) {
      $list = RoomDB::GetOption();
    }
    extract($list);
    self::$stack       = [];
    self::$game_option = $game_option;
    self::$role_option = $option_role;
    self::$max_user    = $max_user;
  }

  //フォーム入力値取得
  public static function LoadPost(...$option_list) {
    foreach ($option_list as $option) {
      $filter = OptionLoader::Load($option);
      if (true === isset($filter)) {
	$filter->LoadPost();
      }
    }
  }

  //登録オプション取得
  public static function Get($type) {
    return ArrayFilter::Concat(self::$$type);
  }

  //スタックから表示順に取得
  public static function GetOrder() {
    if (count(self::$stack) < 1) {
      self::SetStack();
    }
    return array_intersect(RoomOptionOrderData::$list, array_keys(self::$stack));
  }

  //オプション登録
  public static function Set($type, $name) {
    RQ::Set($name, true);
    if (false === in_array($name, self::$$type)) {
      array_push(self::$$type, $name);
    }
  }

  //オプションをパースしてスタック登録
  public static function SetStack() {
    self::$stack = OptionParser::Get(self::$game_option, self::$role_option);
  }

  //ゲームオプション情報生成
  public static function Generate() {
    return self::GenerateImage() . ImageManager::Room()->GenerateMaxUser(self::$max_user);
  }

  //ゲームオプション画像生成
  public static function GenerateImage() {
    $str = '';
    foreach (self::GetOrder() as $option) {
      $str .= OptionLoader::Load($option)->GenerateImage();
    }
    return $str;
  }

  //ゲームオプション画像出力
  public static function Output() {
    self::Load();
    OptionHTML::OutputImage(self::Generate());
  }

  //ゲームオプション説明生成
  public static function OutputCaption() {
    $str = '';
    foreach (self::GetOrder() as $option) {
      $str .= OptionLoader::Load($option)->GenerateRoomCaption();
    }
    echo $str;
  }
}
