<?php
//-- RSS 投稿クラス --//
class JinroRSS {
  const FILE = '/rss/rooms.rss';

  //出力
  static function Output() {
    $file = self::GetFile();
    if (file_exists($file)) {
      $rss = new SiteSummary();
      $rss->Import($file);
    }
    else {
      $rss = self::Update();
    }

    foreach ($rss->items as $item) {
      extract($item, EXTR_PREFIX_ALL, 'room');
      echo $room_description;
    }
  }

  //更新
  static function Update() {
    $file = self::GetFile();
    $rss  = new SiteSummary();
    $rss->Build();

    $fp = fopen($file, 'w');
    fwrite($fp, $rss->Export($file));
    fflush($fp);
    fclose($fp);

    return $rss;
  }

  //ファイル取得
  private static function GetFile() { return JINRO_ROOT . self::FILE; }
}
