<?php
//-- 画像管理クラス --//
class Image {
  private static $room   = null;
  private static $role   = null;
  private static $winner = null;

  //村
  static function Room() {
    if (is_null(self::$room)) self::$room = new RoomImage();
    return self::$room;
  }

  //役職
  static function Role() {
    if (is_null(self::$role)) self::$role = new RoleImage();
    return self::$role;
  }

  //勝利画像
  static function Winner() {
    if (is_null(self::$winner)) self::$winner = new WinnerImage();
    return self::$winner;
  }

  //最大人数の画像生成
  static function GenerateMaxUser($number) {
    return in_array($number, RoomConfig::$max_user_list) && self::Room()->Exists("max{$number}") ?
      self::Room()->Generate("max{$number}", "最大{$number}人") : "(最大{$number}人)";
  }
}

//-- 画像管理の基底クラス --//
class ImageManager {
  const PATH  = '%s/%s/%s.%s';
  const TAG   = '<img src="%s"%s%s>';
  const CSS   = ' class="%s"';
  const TITLE = ' alt="%s" title="%s"';
  const TABLE = '<td>%s</td>';

  //画像の存在確認
  function Exists($name) { return file_exists($this->GetPath($name)); }

  //画像タグ生成
  function Generate($name, $alt = null, $table = false) {
    $css = $this->class == '' ? '' : sprintf(self::CSS, $this->class);
    if (isset($alt)) {
      Text::Escape($alt);
      $title = sprintf(self::TITLE, $alt, $alt);
    }
    else {
      $title = '';
    }
    $str = sprintf(self::TAG, $this->GetPath($name), $css, $title);
    return $table ? sprintf(self::TABLE, $str) : $str;
  }

  //画像出力
  function Output($name) { Text::Output($this->Generate($name), true); }

  //画像のファイルパス取得
  private function GetPath($name) {
    return sprintf(self::PATH, JINROU_IMG, $this->path, $name, $this->extension);
  }
}

//-- 村のオプション画像 --//
class RoomImage extends ImageManager {
  /*
    max[NN].gif という画像が該当パス内にあった場合は村の最大参加人数の表示に使用される。
    例) max8.gif (8人村用)
  */
  public $path      = 'room_option';
  public $extension = 'gif';
  public $class     = 'option';
}

//-- 役職の画像 --//
class RoleImage extends ImageManager {
  public $path      = 'role';
  public $extension = 'gif';
  public $class     = '';
}

//-- 勝利陣営の画像 --//
class WinnerImage extends ImageManager {
  public $path      = 'winner';
  public $extension = 'gif';
  public $class     = 'winner';

  function Generate($name, $alt = null, $table = null) {
    switch ($name) {
    case 'human':
      $alt = '村人勝利';
      break;

    case 'wolf':
      $alt = '人狼勝利';
      break;

    case 'fox1':
    case 'fox2':
      $name = 'fox';
      $alt = '妖狐勝利';
      break;

    case 'lovers':
      $alt = '恋人勝利';
      break;

    case 'quiz':
      $alt = '出題者勝利';
      break;

    case 'vampire':
      $alt = '吸血鬼勝利';
      break;

    case 'draw':
    case 'vanish':
    case 'quiz_dead':
      $name = 'draw';
      $alt = '引き分け';
      break;

    default:
      return '-';
    }
    return parent::Generate($name, $alt);
  }
}
