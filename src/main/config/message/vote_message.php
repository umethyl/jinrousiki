<?php
//-- 投票画面専用メッセージ --//
class VoteMessage {
  /* タイトル */
  const TITLE  = '[投票]';
  const RESULT = '[投票結果]';

  /* 共通 */
  const CAUTION = '* 投票先の変更はできません。慎重に！';	//注意事項
  const SUCCESS = '投票完了';

  /* 開始前 */
  //ゲーム開始投票
  const GAME_START = 'ゲームを開始するに一票';	//投票ボタン
  const GAME_START_TITLE = 'ゲーム開始：';

  //キック処理
  const KICK_DO = '対象をキックするに一票';	//投票ボタン
  const KICK_TITLE   = 'キック投票：';
  const CAUTION_KICK = '* キックするには %d 人の投票が必要です';
  const KICK_SUCCESS = '：%s さん：%d 人目 (Kick するには %d 人以上の投票が必要です)';

  /* 昼 */
  const VOTE_DO = '対象を処刑するに一票';	//投票ボタン
  const REVOTE  = '再投票になりました( %d 回目)';	//再投票メッセージ

  /* 霊界 */
  //蘇生辞退
  const REVIVE_REFUSE = '蘇生を辞退する';	//投票ボタン
  const REVIVE_REFUSE_SUCCESS = 'システム：%sさんは蘇生を辞退しました。';

  //超過時間リセット(管理者用)
  const RESET_TIME = '超過時間リセット';	//投票ボタン
  const RESET_TIME_SUCCESS = 'システム：投票制限時間をリセットしました。';

  /* 配役 */
  const CAMP_HEADER  = '出現陣営：';
  const CAMP_FOOTER  = '陣営';
  const GROUP_HEADER = '出現役職種：';
  const GROUP_FOOTER = '系';
  const ROLE_HEADER  = '出現役職：';

  /* エラー表示 */
  //共通
  const ERROR_TITLE       = '投票エラー [%s]';
  const DB_ERROR          = 'サーバが混雑しています。再度投票をお願いします。';
  const INVALID_COMMAND   = '投票コマンドエラー';
  const INVALID_SCENE     = '投票シーンエラー';
  const INVALID_SITUATION = '無効な投票です。';
  const BUG               = 'プログラムエラーです。管理者に問い合わせてください。';
  const RELOAD            = '戻ってリロードしてください。'; //シーン不一致のリロード要請メッセージ

  //ゲーム終了
  const FINISHED = 'ゲームは終了しました。';
  const FINISHED_TITLE = 'ゲーム終了';

  //空投票
  const NO_TARGET = '投票先を指定してください。';
  const NO_TARGET_TITLE = '空投票';

  //ゲーム開始投票
  const GAME_START_SHORTAGE  = '開始人数に達していません。';
  const GAME_START_DUMMY_BOY = '身代わり君は投票不要です';
  const ALREADY_GAME_START   = '投票済みです';

  //ゲーム開始処理
  const ERROR_CAST = 'ゲームスタート[配役設定エラー]：%s。<br>管理者に問い合わせて下さい。';
  const NO_CAST_LIST         = '%d人は設定されていません';
  const INVALID_CAST         = '不正な配役データです';
  const INVALID_ROLE_COUNT   = '「%s」の人数がマイナスになってます';
  const CAST_MISMATCH_COUNT  = '村人 (%d) と配役の数 (%d) が一致していません';
  const NO_CAST_DUMMY_BOY    = '身代わり君に役が与えられていません';
  const CAST_MISMATCH_REMAIN = '配役未決定者の人数 (%d) と残り配役の数 (%d) が一致していません';
  const CAST_MISMATCH_USER   = '村人の人数 (%d) と配役決定者の人数 (%d) が一致していません';
  const CAST_MISMATCH_ROLE   = '配役決定者の人数 (%d) と配役の数 (%d) が一致していません';
  const CAST_REMAIN_ROLE     = '配役リストに余り (%d) があります';

  //キック
  const KICK_EMPTY     = '投票先が指定されていないか、すでにキックされています。';
  const KICK_DUMMY_BOY = '身代わり君には投票できません';
  const KICK_SELF      = '自分には投票できません';
  const ALREADY_KICK   = ' さんへキック投票済み';

  //昼
  const NEEDLESS_VOTE = '処刑：初日は投票不要です';
  const ALREADY_VOTE  = '処刑：投票済み';
  const INVALID_VOTE  = '処刑：無効な投票先です';
  const VOTE_SELF     = '処刑：自分には投票できません';
  const VOTE_DEAD     = '処刑：死者には投票できません';
  const VOTE_DUEL     = '処刑：決選投票対象者以外には投票できません';

  //夜
  const DUMMY_BOY_NIGHT    = '夜：身代わり君の投票は無効です';
  const ALREAY_VOTE_NIGHT  = '夜：投票済み';
  const VOTE_NIGHT_EMPTY   = '夜：投票イベントが空です';
  const INVALID_VOTE_NIGHT = '夜：投票イベントが一致しません';

  //蘇生辞退
  const ALREADY_DROP = '蘇生辞退：投票済み';
  const ALREADY_OPEN = '蘇生辞退：投票不要です';
}
