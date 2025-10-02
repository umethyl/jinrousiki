<?php
/*
  ◆霊界で配役を公開しない (not_open_cast)
*/
OptionManager::Load('not_close_cast');
class Option_not_open_cast extends Option_not_close_cast {
  public function GetCaption() {
    return '霊界で配役を公開しない';
  }

  public function GetExplain() {
    return '常時非公開 (誰がどの役職なのか公開されません。蘇生能力は有効です)';
  }
}
