<?php
//-- ロガーコントローラークラス --//
class JinrouLogger {
  private static $instance = null;

  //ロガーロード
  public static function Load() {
    if (null === self::$instance) {
      self::$instance = new Paparazzi();
    }
    return self::$instance;
  }

  //ロガー取得
  public static function Get() {
    return self::$instance;
  }
}

//コメントとカテゴリを指定して、ログに新しい行を追加します。
//引数
//$comment  : ログに追加するメッセージの本体を指定します。
//$category : ログに追加するデータの分類名を指定します。この引数は省略可能です。
//            指定しなかった場合、規定値として'general'が設定されます。
function shot($comment, $category = 'general') {
  return JinrouLogger::Get()->shot($comment, $category);
}
