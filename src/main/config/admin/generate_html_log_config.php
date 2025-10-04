<?php
//-- ログのHTML化詳細設定 --//
final class GenerateHTMLLogConfig {
  /*
    機能を有効にするには AdminConfig::$generate_html_log_enable を true に設定してください
  */

  /* 動作モード */
  /*
    room:   個別の部屋ログ生成
    index:  一覧ページの生成
    delete: 部屋の削除 (元に戻せないので慎重に！)
  */
  const MODE = 'room';

  /* 出力設定 */
  //出力先ディレクトリ(あらかじめ空ディレクトリを作成しておいてください)
  const DIR = 'log';

  //各ファイルの先頭につける文字列 (テスト/上書き回避用)
  const PREFIX = '';

  /* 一覧ページ設定 */
  //一覧ページの開始番号
  const INDEX_START = 1;

  /* 個別の部屋設定 */
  //HTML化する村の開始番号
  const ROOM_START = 1;

  //HTML化する村の終了番号
  const ROOM_END   = 2;
}
