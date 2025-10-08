<?php
/*
  ◆天啓封印 (seal_message)
  ○仕様
  ・システムメッセージ：出力・閲覧封印
*/
class Option_seal_message extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '天啓封印';
  }

  public function GetExplain() {
    return '一部の個人通知メッセージが表示されなくなります';
  }

  //天啓封印対象判定
  /*
    ・解呪成功 (陰陽師)
    ・護衛成功 (夢守人・護衛能力者)
    ・狩り成功 (狩り能力者)
    ・護衛貫通付加 (一寸法師)
    ・厄払い成功 (厄神)
    ・妖狐襲撃 (人狼)
    ・襲撃回避 (鋭狼)
    ・蘇生結果 (蘇生能力者)
   */
  public function IsSealMessage($type) {
    switch ($type) {
    case RoleAbility::VOODOO_KILLER:
    case RoleAbility::GUARD:
    case RoleAbility::HUNTED:
    case RoleAbility::PENETRATION:
    case RoleAbility::ANTI_VOODOO:
    case RoleAbility::FOX:
    case RoleAbility::SHARP_WOLF:
    case RoleAbility::REVIVE:
      return true;

    default:
      return false;
    }
  }
}
