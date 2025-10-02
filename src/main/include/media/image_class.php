<?php
//-- 画像管理クラス --//
class ImageManager {
  //村
  public static function Room() {
    static $filter;

    if (is_null($filter)) {
      $filter = new RoomImage();
    }
    return $filter;
  }

  //役職
  public static function Role() {
    static $filter;

    if (is_null($filter)) {
      $filter = new RoleImage();
    }
    return $filter;
  }

  //勝利画像
  public static function Winner() {
    static $filter;

    if (is_null($filter)) {
      $filter = new WinnerImage();
    }
    return $filter;
  }
}

//-- 画像管理の基底クラス --//
abstract class Image {
  const PATH = '%s/%s/%s.%s';

  //画像の存在確認
  final public function Exists($name) {
    return file_exists($this->GetPath($name));
  }

  //画像タグ生成
  public function Generate($name, $alt = null, $table = false) {
    $css = $this->class == '' ? '' : ImageHTML::GenerateCSS($this->class);
    if (isset($alt)) {
      Text::Escape($alt);
      $title = ImageHTML::GenerateTitle($alt, $alt);
    } else {
      $title = '';
    }
    $str = ImageHTML::Generate($this->GetPath($name), $css, $title);
    return $table ? TableHTML::GenerateTd($str) : $str;
  }

  //画像出力
  final public function Output($name) {
    Text::Output($this->Generate($name), true);
  }

  //出力 (存在確認対応版)
  final public function OutputExists($name) {
    if ($this->Exists($name)) $this->Output($name);
  }

  //画像のファイルパス取得
  private function GetPath($name) {
    return sprintf(self::PATH, JINROU_IMG, $this->path, $name, $this->extension);
  }
}

//-- 村のオプション画像 --//
class RoomImage extends Image {
  /*
    max[NN].gif という画像が該当パス内にあった場合は村の最大参加人数の表示に使用される。
    例) max8.gif (8人村用)
  */
  public $path      = 'room_option';
  public $extension = 'gif';
  public $class     = 'option';

  //最大人数の画像生成
  public function GenerateMaxUser($number) {
    $name = 'max' . $number;
    $alt  = sprintf(GameMessage::ROOM_MAX_USER, $number);
    return in_array($number, RoomConfig::$max_user_list) && $this->Exists($name) ?
      $this->Generate($name, $alt) : $alt;
  }
}

//-- 役職の画像 --//
class RoleImage extends Image {
  public $path      = 'role';
  public $extension = 'gif';
  public $class     = '';
}

//-- 勝利陣営の画像 --//
class WinnerImage extends Image {
  public $path      = 'winner';
  public $extension = 'gif';
  public $class     = 'winner';

  public function Generate($name, $alt = null, $table = false) {
    switch ($name) {
    case WinCamp::HUMAN:
    case WinCamp::WOLF:
    case WinCamp::LOVERS:
    case WinCamp::QUIZ:
    case WinCamp::VAMPIRE:
      break;

    case WinCamp::FOX_HUMAN:
    case WinCamp::FOX_WOLF:
      $name = WinCamp::FOX;
      break;

    case WinCamp::DRAW:
    case WinCamp::VANISH:
    case WinCamp::QUIZ_DEAD:
      $name = WinCamp::DRAW;
      break;

    default:
      return '-';
    }
    return parent::Generate($name, WinnerMessage::${'image_' . $name});
  }
}
