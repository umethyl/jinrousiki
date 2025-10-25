<?php
/*
  ◆霊界で配役を公開しない (not_open_cast)
  ・霊界公開判定：身代わり君蘇生辞退判定実行 + ユーザー霊界公開判定
*/
OptionLoader::LoadFile('not_close_cast');
class Option_not_open_cast extends Option_not_close_cast {
  public function GetCaption() {
    return '霊界で配役を公開しない';
  }

  public function GetExplain() {
    return '常時非公開 (誰がどの役職なのか公開されません。蘇生能力は有効です)';
  }

  public function IsRoomOpenCast() {
    $user = DB::$USER->ByID(GM::ID); //身代わり君の蘇生辞退判定
    return $user->IsDummyBoy() && $user->IsDrop() && $this->IsUserOpenCast();
  }
}
