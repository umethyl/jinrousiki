<?php
/*
  ◆霊界自動公開 (auto_open_cast)
*/
OptionManager::Load('not_close_cast');
class Option_auto_open_cast extends Option_not_close_cast {
  function GetCaption() { return '自動で霊界の配役を公開する'; }

  function GetExplain() {
    return '自動公開 (蘇生能力者などが能力を持っている間だけ霊界が非公開になります)';
  }
}
