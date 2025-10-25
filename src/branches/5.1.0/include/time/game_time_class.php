<?php
//-- 日時関連 (Game 拡張) --//
final class GameTime {
  /* 取得系 */
  //経過時間取得
  public static function GetPass() {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      return self::GetRealPass($left_time);
    } else {
      return self::GetTalkPass($left_time);
    }
  }

  //経過時間取得 (リアルタイム制)
  public static function GetRealPass(&$left_time) {
    $start_time = DB::$ROOM->scene_start_time; //シーン開始時刻
    $base_time  = Time::ByMinute(DB::$ROOM->real_time->{DB::$ROOM->scene}); //設定された制限時間
    $pass_time  = DB::$ROOM->system_time - $start_time;
    if (DB::$ROOM->IsOption('wait_morning') && DB::$ROOM->IsDay()) { //早朝待機制
      $base_time += TimeConfig::WAIT_MORNING; //制限時間を追加する
      //待機判定
      DB::$ROOM->Stack()->Get('event')->Set('wait_morning', $pass_time <= TimeConfig::WAIT_MORNING);
    }
    $left_time = max(0, $base_time - $pass_time); //残り時間
    return $start_time + $base_time;
  }

  //経過時間取得 (仮想時間制)
  public static function GetTalkPass(&$left_time, $silence = false) {
    if (DB::$ROOM->IsDay()) { //昼は12時間
      $base_time = TimeConfig::DAY;
      $full_time = 12;
    } else { //夜は6時間
      $base_time = TimeConfig::NIGHT;
      $full_time = 6;
    }
    $spend_time     = TalkDB::GetSpendTime();
    $left_time      = max(0, $base_time - $spend_time); //残り時間
    $base_left_time = $silence ? TimeConfig::SILENCE_PASS : $left_time; //仮想時間の計算
    return Time::Convert(Time::ByHour($full_time * $base_left_time) / $base_time);
  }

  //残り時間取得
  public static function GetLeftTime() {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制
      self::GetRealPass($left_time);
    } else {
      self::GetTalkPass($left_time);
    }
    return $left_time;
  }

  //仮想時間制の発言量経過時間取得
  public static function GetSpendTime($str) {
    if (DB::$ROOM->IsRealTime()) { //リアルタイム制は無効にする
      return 0;
    } else {
      return min(4, max(1, floor(strlen($str) / 100))); //範囲は 1 - 4
    }
  }

  /* 判定系 */
  //超過前
  public static function IsInTime() {
    return self::GetLeftTime() > 0;
  }

  /* 変換系 */
  //JavaScript の Date() オブジェクト作成コード生成
  public static function ConvertJavaScriptDate($time) {
    $stack = Text::Parse(Time::GetDate('Y,m,j,G,i,s', $time), ',');
    $stack[1]--;  //JavaScript の Date() の Month は 0 からスタートする
    return sprintf('new Date(%s)', ArrayFilter::ToCSV($stack));
  }
}
