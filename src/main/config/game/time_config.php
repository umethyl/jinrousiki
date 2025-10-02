<?php
//ゲームの時間設定
class TimeConfig {
  /* 未投票超過 */
  //突然死処理待機時間 (秒) (この時間を過ぎても未投票の人がいたら突然死処理されます)
  const SUDDEN_DEATH = 180;

  //サーバダウン判定時間 (秒)
  /*
    超過のマイナス時間がこの閾値を越えた場合はサーバが一時的にダウンしていたと判定して、
    超過時間をリセットします
  */
  const SERVER_DISCONNECT = 90;

  /* 警告音 */
  const ALERT = 90; //超過の残り時間がこの時間を切っても未投票の人がいたら警告音が鳴ります (秒)
  const ALERT_DISTANCE = 6; //警告音の鳴る間隔 (秒)

  /* リアルタイム制 */
  const DEFAULT_DAY   =  5; //昼の制限時間の初期値 (分)
  const DEFAULT_NIGHT =  3; //夜の制限時間の初期値 (分)
  const WAIT_MORNING  = 15; //早朝待機制の待機時間 (秒)

  /* 会話を用いた仮想時間制 */
  /*
    昼は12時間、spend_time=1(半角100文字以内) で 12時間 ÷ DAY   進みます
    夜は 6時間、spend_time=1(半角100文字以内) で  6時間 ÷ NIGHT 進みます
    沈黙経過時間 (12時間 ÷ DAY (昼) / 6時間 ÷ NIGHT (夜) の SILENCE_PASS 倍の時間が進みます)
  */
  const DAY   = 96;
  const NIGHT = 24;
  const SILENCE_PASS = 8;
  const SILENCE = 60; //この閾値を過ぎると沈黙となり、設定した時間が進みます(秒)
}
