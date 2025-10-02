<?php
//-- Setup 専用メッセージ --//
class SetupMessage {
  /* タイトル */
  const TITLE = ' [初期設定]';

  /* データベース */
  const TARGET_DB = '対象データベース';
  const CREATE_DB = 'データベース作成';

  /* テーブル */
  const CREATE_TABLE = 'テーブル作成';
  const DROP_TABLE   = 'テーブル削除';

  /* インデックス */
  const CREATE_INDEX     = 'インデックス生成';
  const REGENERATE_INDEX = 'インデックス再生成';

  /* カラム */
  const CHANGE_COLUMN = 'カラム変更: ';
  const DROP_COLUMN   = 'カラム削除: ';

  /* アイコン */
  const ICON = 'ユーザアイコン登録';

  /* 成否 */
  const SUCCESS  = '成功';
  const FAILED   = '失敗';
  const FINISHED = '設定完了';
  const COMPLETE = '初期設定の処理が終了しました';
  const ALREADY  = '設定完了済み';
}
