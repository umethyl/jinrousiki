<?php
//-- GamePlay 専用メッセージ --//
class GamePlayMessage {
  /* ヘッダーリンク */
  public static $header_play_sound    = '音';
  public static $header_icon          = 'アイコン';
  public static $header_name          = '名前';
  public static $header_async         = '非同期';
  public static $header_list_down_on  = '↓リスト';
  public static $header_list_down_off = '↑リスト';
  public static $header_describe_room = 'OP';
  public static $header_game_play     = '別ページ';
  public static $header_game_view     = '観戦';
  public static $header_user_manager  = '登録情報変更';
  public static $header_room_manager  = '村オプション変更';

  /* ゲーム開始前 */
  const BEFOREGAME_CAUTION = 'ゲームを開始するには全員がゲーム開始に投票する必要があります';
  const BEFOREGAME_VOTE   = '(投票した人は村人リストの背景が赤くなります)';

  /* 霊界 */
  const HEAVEN_TITLE = '&lt;&lt;&lt;幽霊の間&gt;&gt;&gt;';
  const RELOAD       = '更新'; //下界更新ボタン

  /* 時間設定 */
  const REAL_TIME    = '設定時間：昼<span>%d分</span> / 夜<span>%d分</span>';
  const SUDDEN_DEATH = '　突然死：<span>%s</span>';

  /* 未投票突然死 */
  //const SUDDEN_DEATH_ALERT = 'あと%sで投票完了されない方は死して地獄へ堕ちてしまいます';
  //const SUDDEN_DEATH_TIME  = '突然死になるまで後：';
  const SUDDEN_DEATH_ALERT = 'あと%sで投票完了されない方はスキマ送りされてしまいます';
  const SUDDEN_DEATH_TIME  = 'スキマ送りされるまで後：';
  const NOVOTED_COUNT      = '未投票：%d人';

  /* 異議あり */
  const OBJECTION = '異議あり';

  /* 発言 */
  const LIMIT_SAY  = '文字数または行数が多すぎたので発言できませんでした'; //発言上限
  const LIMIT_TALK = '今日の発言数制限に達したので発言できませんでした'; //発言数制限制
  const SILENCE    = '・・・・・・・・・・ %s ほどの沈黙が続いた'; //沈黙判定 (会話で時間経過制)
  const TALK_COUNT = '発言数制限';

  /* 投票情報 (クイズ村 GM 専用) */
  const QUIZ_VOTED_NAME  = '名前';
  const QUIZ_VOTED_COUNT = '得票数';

  /* 遺言 */
  const LAST_WORDS = '自分の遺言';
}
