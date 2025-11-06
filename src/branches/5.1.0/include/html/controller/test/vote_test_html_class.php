<?php
//-- HTML 生成クラス (投票テスト拡張) --//
//-- ◆文字化け抑制◆ --//
final class VoteTestHTML {
  //配役情報出力
  public static function OutputCast() {
    HTML::OutputHeader(VoteTestMessage::TITLE, 'game_play', true);
    Text::Printf(self::GetCastHeader(), VoteTestMessage::CAST_POPULATION);
    foreach (ChaosConfig::$role_group_rate_list as $group => $rate) {
      $role = RoleDataManager::GetGroup($group);
      TableHTML::OutputTh(RoleDataManager::GetShortName($role), RoleDataManager::GetCSS($role));
    }
    TableHTML::OutputTrFooter();
    for ($i = 8; $i <= 40; $i++) {
      TableHTML::OutputTrHeader(null, 'right');
      TableHTML::OutputTh($i);
      foreach (ChaosConfig::$role_group_rate_list as $rate) {
	TableHTML::OutputTd(round($i / $rate));
      }
      TableHTML::OutputTrFooter();
    }
    TableHTML::OutputFooter(false);
    HTML::OutputFooter(true);
  }

  //前日の能力発動結果出力
  public static function OutputAbilityAction() {
    //昼間で役職公開が許可されているときのみ表示
    if (! DB::$ROOM->IsDay() || ! (DB::$SELF->IsDummyBoy() || DB::$ROOM->IsOpenCast())) {
      return false;
    }

    foreach (RQ::GetTest()->vote->night as $stack) {
      printf(self::GetAbilityActionHeader(),
	VoteTestMessage::ABILITY_HEADER,
	DB::$USER->ByID($stack['user_no'])->GenerateShortRoleName(false, true)
      );
      $target = '';
      switch ($stack['type']) {
      case VoteAction::CUPID:
      case VoteAction::STEP_MAGE:
      case VoteAction::STEP_GUARD:
      case VoteAction::STEP_ASSASSIN:
      case VoteAction::PLURAL_WIZARD:
      case VoteAction::SPARK_WIZARD:
      case VoteAction::SPREAD_WIZARD:
      case VoteAction::STEP_WOLF:
      case VoteAction::SILENT_WOLF:
      case VoteAction::STEP:
      case VoteAction::STEP_VAMPIRE:
	$target_stack = [];
	foreach (Text::Parse($stack[RequestDataVote::TARGET]) as $id) {
	  $user = DB::$USER->ByVirtual($id);
	  $target_stack[$user->id] = $user->GenerateShortRoleName(false, true);
	}
	ksort($target_stack);
	$target = ArrayFilter::Concat($target_stack);
	break;

      default:
	if (isset($stack[RequestDataVote::TARGET])) {
	  $id     = $stack[RequestDataVote::TARGET];
	  $target = DB::$USER->ByVirtual($id)->GenerateShortRoleName(false, true);
	}
	break;
      }
      if (! empty($target)) {
	echo VoteTestMessage::ABILITY_TARGET . $target;
      }

      switch ($stack['type']) {
      case VoteAction::GUARD:
      case VoteAction::REPORTER:
      case VoteAction::ASSASSIN:
      case VoteAction::WIZARD:
      case VoteAction::ESCAPE:
      case VoteAction::WOLF:
      case VoteAction::STEP:
      case VoteAction::DREAM:
      case VoteAction::EXIT_DO:
      case VoteAction::NOT_EXIT:
      case VoteAction::CUPID:
      case VoteAction::VAMPIRE:
      case VoteAction::FAIRY:
      case VoteAction::OGRE:
      case VoteAction::DUELIST:
      case VoteAction::TENGU:
      case VoteAction::DEATH_NOTE:
      case VoteAction::NOT_ASSASSIN:
      case VoteAction::NOT_POSSESSED:
      case VoteAction::NOT_OGRE:
      case VoteAction::NOT_DEATH_NOTE:
        $type = $stack['type'];
	echo VoteRoleMessage::$$type;
	break;

      case VoteAction::REVIVE:
	echo VoteRoleMessage::$POISON_CAT_DO;
	break;

      case VoteAction::NOT_REVIVE:
	echo VoteRoleMessage::$POISON_CAT_NOT_DO;
	break;

      case VoteAction::PLURAL_WIZARD:
      case VoteAction::SPARK_WIZARD:
      case VoteAction::SPREAD_WIZARD:
	echo VoteRoleMessage::$WIZARD_DO;
	break;

      case VoteAction::TRAP:
	echo VoteRoleMessage::$TRAP_MAD_DO;
	break;

      case VoteAction::NOT_TRAP:
	echo VoteRoleMessage::$TRAP_MAD_NOT_DO;
	break;

      case VoteAction::STEP_GUARD:
	echo VoteRoleMessage::$GUARD_DO;
	break;

      case VoteAction::MAGE:
      case VoteAction::STEP_MAGE:
      case VoteAction::CHILD_FOX:
	echo VoteTestMessage::ABILITY_MAGE_DO;
	break;

      case VoteAction::VOODOO_KILLER:
	echo VoteTestMessage::ABILITY_VOODOO_KILLER_DO;
	break;

      case VoteAction::ANTI_VOODOO:
	echo VoteTestMessage::ABILITY_ANTI_VOODOO_DO;
	break;

      case VoteAction::SCAN:
	echo VoteTestMessage::ABILITY_MIND_SCANNER_DO;
	break;

      case VoteAction::JAMMER:
	echo VoteTestMessage::ABILITY_JAMMER_DO;
	break;

      case VoteAction::VOODOO_MAD:
      case VoteAction::VOODOO_FOX:
	echo VoteTestMessage::ABILITY_VOODOO_DO;
	break;

      case VoteAction::MANIA:
	echo VoteTestMessage::ABILITY_MANIA_DO;
	break;

      case VoteAction::STEP_ASSASSIN:
      case VoteAction::STEP_WOLF:
      case VoteAction::SILENT_WOLF:
      case VoteAction::POSSESSED:
      case VoteAction::STEP_VAMPIRE:
	echo VoteTestMessage::ABILITY_TARGETED;
	break;
      }
      Text::Output(HTML::GenerateTagFooter('b'), true);
    }
  }

  private static function GetCastHeader() {
    return <<<EOF
<table border="1" cellspacing="0">
<tr><th>%s</th>
EOF;
  }

  //能力発動結果ヘッダ
  private static function GetAbilityActionHeader() {
    return '<b>%s%s ';
  }
}
