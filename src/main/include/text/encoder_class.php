<?php
//-- 文字コード関連 --//
final class Encoder {
  //変換
  public static function Convert($str, $encode, $convert = ServerConfig::ENCODE) {
    if ($encode == '' || $encode == 'ASCII' || $encode == $convert) {
      return $str;
    } else {
      return mb_convert_encoding($str, $convert, $encode);
    }
  }

  //BOM 消去
  public static function BOM(string $str) {
    if (ord($str[0]) == '0xef' && ord($str[1]) == '0xbb' && ord($str[2]) == '0xbf') {
      $str = substr($str, 3);
    }
    return $str;
  }

  //POST されたデータの文字コードを統一する
  public static function Post() {
    self::Filter($_POST);
  }

  //配列データフィルタリング (POST 変換用)
  private static function Filter(array &$list) {
    foreach ($list as $key => $value) {
      //多段配列対応(例: アイコンのカテゴリ)
      if (is_array($value)) {
	self::Filter($value);
      } else {
	$list[$key] = self::Convert($value, self::Detect($value));
      }
    }
  }

  //文字コード判定
  public static function Detect(string $str) {
    if (self::UTF($str)) {
      return 'UTF-8';
    } else {
      return @mb_detect_encoding($str, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
    }
  }

  //UTF-8判定
  private static function UTF(string $str) {
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
      $c = ord($str[$i]);
      if ($c > 128) {
	if ($c > 247) {
	  return false;
	} elseif ($c > 239) {
	  $bytes = 4;
	} elseif ($c > 223) {
	  $bytes = 3;
	} elseif ($c > 191) {
	  $bytes = 2;
	} else {
	  return false;
	}
	if (($i + $bytes) > $len) {
	  return false;
	}
	while ($bytes > 1) {
	  $i++;
	  if (Number::OutRange(ord($str[$i]), 128, 191)) {
	    return false;
	  }
	  $bytes--;
	}
      }
    }
    return true;
  }
}
