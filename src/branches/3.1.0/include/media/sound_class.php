<?php
//-- 音源処理クラス --//
class Sound {
  const PATH = '%s/%s/%s.%s';

  //ファイルパス取得
  public static function GetPath($type) {
    return sprintf(self::PATH,
      JINROU_ROOT, SoundConfig::PATH, SoundConfig::$$type, SoundConfig::EXTENSION
    );
  }
}
