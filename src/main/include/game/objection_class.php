<?php
//-- ◆文字化け抑制◆ --//
//-- 「異議」あり関連クラス --//
final class Objection {
  //クッキー用情報取得
  public static function GetCookie() {
    //KICK も含めたユーザ総数から配列をセット (index は 0 から)
    $stack = array_fill(0, DB::$USER->CountAll(), 0);

    //ユーザ全体の「異議」ありを集計
    $count = 0;
    foreach (DB::$USER->GetName() as $uname => $id) {
      $stack[$count++] = DB::$USER->ByID($id)->objection;
    }
    return $stack;
  }

  //会話メッセージ取得
  public static function GetTalk($sex) {
    $str = Text::AddFooter(TalkAction::OBJECTION, strtoupper($sex));
    return VoteTalkMessage::$$str;
  }

  //画像パス取得
  public static function GetImage() {
    return GameConfig::OBJECTION_IMAGE . 'objection_' . Sex::Get(self::GetUser()) . '.gif';
  }

  //残り回数取得
  public static function Count() {
    return GameConfig::OBJECTION - self::GetUser()->objection;
  }

  //セット判定
  public static function Set() {
    $user = self::GetUser();
    if (self::IsSetUser($user) && self::IsSetScene()) {
      $user->objection++;
      $user->Update('objection', $user->objection);

      $talk = new RoomTalkStruct(Sex::Get($user));
      $talk->Set(TalkStruct::UNAME,  $user->uname);
      $talk->Set(TalkStruct::ACTION, TalkAction::OBJECTION);
      DB::$ROOM->Talk($talk);
    }
  }

  //音声出力
  public static function OutputSound() {
    $cookie = Text::Parse(JinrouCookie::$objection, ','); //クッキーの値を配列に格納する
    $stack  = JinrouCookie::$objection_list;
    $count  = count($stack);
    if (count($cookie) == $count) {
      for ($i = 0; $i < $count; $i++) { //差分を計算 (index は 0 から)
	//差分があれば性別を確認して音を鳴らす
	if (isset($cookie[$i]) && $stack[$i] > $cookie[$i]) {
	  SoundHTML::Output('objection_' . Sex::Get(DB::$USER->ByID($i + 1)));
	}
      }
    }
  }

  //ユーザ取得
  private static function GetUser() {
    return DB::$SELF->GetVirtual(); //情報は憑依先を参照する
  }

  //セット有効判定 (ユーザ)
  private static function IsSetUser(User $user) {
    return RQ::Fetch()->set_objection && $user->objection < GameConfig::OBJECTION;
  }

  //セット有効判定 (シーン)
  private static function IsSetScene() {
    return DB::$ROOM->IsBeforeGame() ||
      (DB::$ROOM->IsDay() && DB::$SELF->IsLive() && GameTime::IsInTime());
  }
}
