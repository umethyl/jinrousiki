<?php
//-- HTML 生成クラス (Role 拡張) --//
class RoleHTML {
  //能力の種類とその説明を出力
  public static function OutputAbility() {
    if (! DB::$ROOM->IsPlaying()) return false; //ゲーム中のみ表示する

    if (DB::$SELF->IsDead()) { //死亡したら口寄せ以外は表示しない
      echo HTML::GenerateSpan(RoleAbilityMessage::DEAD, 'ability ability-dead') . Text::BR;
      if (DB::$SELF->IsRole('mind_evoke')) ImageManager::Role()->Output('mind_evoke');
      if (DB::$SELF->IsDummyBoy() && ! DB::$ROOM->IsOpenCast()) { //身代わり君のみ隠蔽情報を表示
	GameHTML::OutputVoteAnnounce(GameMessage::CLOSE_CAST);
      }
      return;
    }
    RoleLoader::LoadMain(DB::$SELF)->OutputAbility(); //メイン役職

    //-- ここからサブ役職 --//
    foreach (RoleLoader::LoadType('display_real') as $filter) {
      $filter->OutputAbility();
    }

    //-- ここからは憑依先の役職を表示 --//
    foreach (RoleLoader::LoadUser(DB::$SELF->GetVirtual(), 'display_virtual') as $filter) {
      $filter->OutputAbility();
    }

    //-- これ以降はサブ役職非公開オプションの影響を受ける --//
    if (DB::$ROOM->IsOption('secret_sub_role')) return;

    foreach (RoleDataManager::GetDisplayList(RoleLoader::GetActor()->GetSubRoleList()) as $role) {
      RoleLoader::Load($role)->OutputAbility();
    }
  }

  //仲間表示
  public static function OutputPartner(array $list, $header, $footer = null) {
    if (count($list) < 1) return; //仲間がいなければ表示しない

    $list[] = TableHTML::GenerateTdFooter();
    $str    = ArrayFilter::Concat($list, RoleAbilityMessage::HONORIFIC . Message::SPACER);
    $stack  = [
      TableHTML::GenerateHeader('ability-partner'),
      ImageManager::Role()->Generate($header, null, true),
      TableHTML::GenerateTdHeader() . Message::SPACER . $str
    ];
    if ($footer) $stack[] = ImageManager::Role()->Generate($footer, null, true);
    $stack[] = TableHTML::GenerateFooter();
    Text::Output(ArrayFilter::Concat($stack, Text::LF));
  }

  //現在の憑依先表示
  public static function OutputPossessed() {
    $type = 'possessed_target';
    if (is_null($stack = DB::$SELF->GetPartner($type))) return;

    $target = DB::$USER->ByID(ArrayFilter::GetMaxKey($stack))->handle_name;
    if ($target != '') self::OutputAbilityResult('partner_header', $target, $type);
  }

  //処刑投票メッセージ出力
  public static function OutputVoteKill() {
    if (DB::$ROOM->date < 2 || ! DB::$ROOM->IsDay() || DB::$SELF->IsDead()) return; //スキップ判定

    $vote_count = sprintf(RoleAbilityMessage::VOTE_COUNT, DB::$ROOM->revote_count + 1);
    if (is_null(DB::$SELF->target_no)) {
      $result = HTML::GenerateWarning(RoleAbilityMessage::NOT_VOTED);
      HTML::OutputDiv($vote_count . $result, 'self-vote');
      echo HTML::GenerateSpan(RoleAbilityMessage::VOTE_KILL, 'ability vote-do') . Text::BRLF;
    } else {
      $user   = DB::$USER->ByVirtual(DB::$SELF->target_no);
      $result = $user->handle_name . RoleAbilityMessage::SETTLE_VOTED;
      HTML::OutputDiv($vote_count . $result, 'self-vote');
    }
  }

  //夜の未投票メッセージ出力
  public static function OutputVote($class, $str, $type, $not_type = '') {
    $stack = DB::$ROOM->IsTest() ? [] : DB::$SELF->LoadVote($type, $not_type);
    if (count($stack) > 0) {
      $str = self::GetVoteMessage($stack, $type, $not_type);
    }
    echo HTML::GenerateSpan($str, 'ability ' . $class) . Text::BRLF;
  }

  //能力発動結果表示
  public static function OutputResult($action) {
    $header = null;
    $footer = 'result_';
    $limit  = false;
    $uniq   = false;
    switch ($action) {
    case RoleAbility::MAGE:
    case RoleAbility::CHILD_FOX:
      $type   = RoleAbility::MAGE;
      $header = 'mage_result';
      $limit  = true;
      break;

    case RoleAbility::VOODOO_KILLER:
      $type   = RoleAbility::MAGE;
      $footer = 'voodoo_killer_';
      $limit  = true;
      break;

    case RoleAbility::NECROMANCER:
    case RoleAbility::SOUL_NECROMANCER:
    case RoleAbility::PSYCHO_NECROMANCER:
    case RoleAbility::EMBALM_NECROMANCER:
    case RoleAbility::ATTEMPT_NECROMANCER:
    case RoleAbility::DUMMY_NECROMANCER:
    case RoleAbility::MIMIC_WIZARD:
    case RoleAbility::SPIRITISM_WIZARD:
    case RoleAbility::MONK_FOX:
      $type   = RoleAbility::NECROMANCER;
      $header = 'necromancer';
      break;

    case RoleAbility::EMISSARY_NECROMANCER:
      $type   = RoleAbility::PRIEST;
      $header = 'emissary_necromancer_header';
      $footer = 'priest_footer';
      break;

    case RoleAbility::MEDIUM:
      $type   = RoleAbility::NECROMANCER;
      $header = 'medium';
      break;

    case RoleAbility::PRIEST:
    case RoleAbility::DUMMY_PRIEST:
    case RoleAbility::PRIEST_JEALOUSY:
      $type   = RoleAbility::PRIEST;
      $header = 'priest_header';
      $footer = 'priest_footer';
      break;

    case RoleAbility::BISHOP_PRIEST:
      $type   = RoleAbility::PRIEST;
      $header = 'bishop_priest_header';
      $footer = 'priest_footer';
      break;

    case RoleAbility::DOWSER_PRIEST:
      $type   = RoleAbility::PRIEST;
      $header = 'dowser_priest_header';
      $footer = 'dowser_priest_footer';
      break;

    case RoleAbility::WEATHER_PRIEST:
      $type   = RoleAbility::WEATHER_PRIEST;
      $header = 'weather_priest_header';
      break;

    case RoleAbility::CRISIS_PRIEST:
      $type   = RoleAbility::CRISIS_PRIEST;
      $header = 'side_';
      $footer = 'crisis_priest_result';
      break;

    case RoleAbility::HOLY_PRIEST:
      $type   = RoleAbility::PRIEST;
      $header = 'holy_priest_header';
      $footer = 'dowser_priest_footer';
      $limit  = true;
      break;

    case RoleAbility::BORDER_PRIEST:
      $type   = RoleAbility::PRIEST;
      $header = 'border_priest_header';
      $footer = 'priest_footer';
      $limit  = true;
      break;

    case RoleAbility::GUARD:
    case RoleAbility::HUNTED:
    case RoleAbility::PENETRATION:
      $type   = RoleAbility::MAGE;
      $footer = 'guard_';
      $limit  = true;
      break;

    case RoleAbility::REPORTER:
      $type   = RoleAbility::REPORTER;
      $header = 'reporter_result_header';
      $footer = 'reporter_result_footer';
      $limit  = true;
      break;

    case RoleAbility::ANTI_VOODOO:
      $type   = RoleAbility::MAGE;
      $footer = 'anti_voodoo_';
      $limit  = true;
      break;

    case RoleAbility::REVIVE:
      $type   = RoleAbility::MAGE;
      $footer = 'poison_cat_';
      $limit  = true;
      break;

    case RoleAbility::PHARMACIST:
      $type   = RoleAbility::MAGE;
      $footer = 'pharmacist_';
      $limit  = true;
      break;

    case RoleAbility::ASSASSIN:
      $type   = RoleAbility::MAGE;
      $header = 'assassin_result';
      $limit  = true;
      break;

    case RoleAbility::CLAIRVOYANCE:
      $type   = RoleAbility::REPORTER;
      $header = 'clairvoyance_result_header';
      $footer = 'clairvoyance_result_footer';
      $limit  = true;
      $uniq   = true;
      break;

    case RoleAbility::SEX_WOLF:
    case RoleAbility::SHARP_WOLF:
    case RoleAbility::TONGUE_WOLF:
      $type   = RoleAbility::MAGE;
      $header = 'wolf_result';
      $limit  = true;
      break;

    case RoleAbility::FOX:
      $type   = RoleAbility::FOX;
      $header = 'fox_';
      $limit  = true;
      break;

    case RoleAbility::VAMPIRE:
      $type   = RoleAbility::MAGE;
      $header = 'vampire_result';
      $limit  = true;
      break;

    case RoleAbility::PATRON:
    case RoleAbility::MANIA:
      $type  = RoleAbility::MAGE;
      $limit = true;
      break;

    case RoleAbility::TENGU_CAMP:
      $type = RoleAbility::WEATHER_PRIEST;
      break;

    case RoleAbility::TENGU:
      $type   = RoleAbility::MAGE;
      $header = 'tengu_result';
      $limit  = true;
      break;

    case RoleAbility::PRIEST_TENGU:
      $type   = RoleAbility::PRIEST;
      $header = 'priest_tengu_header';
      $footer = 'priest_footer';
      break;

    case RoleAbility::SYMPATHY:
      $type   = RoleAbility::MAGE;
      $header = 'sympathy_result';
      $limit  = ! DB::$SELF->IsRole('ark_angel');
      $uniq   = true;
      break;

    case RoleAbility::PRESAGE:
      $type   = RoleAbility::REPORTER;
      $header = 'presage_result_header';
      $footer = 'reporter_result_footer';
      $limit  = true;
      break;

    default:
      if (DB::$ROOM->IsTest()) Text::p($action, '★Invalid Action');
      return false;
    }

    $target_date = DB::$ROOM->date - 1;
    if (DB::$ROOM->IsTest()) {
      $result_list = DevRoom::GetAbility($target_date, $action, $limit);
    } else {
      $result_list = SystemMessageDB::GetAbility($target_date, $action, $limit);
    }
    //Text::p($result_list, '◆Result');

    switch ($type) {
    case RoleAbility::MAGE:
      if ($uniq) $stack = [];
      foreach ($result_list as $result) {
	if ($uniq && in_array($result['target'], $stack)) continue;
	self::OutputAbilityResult($header, $result['target'], $footer . $result['result']);
	if ($uniq) $stack[] = $result['target'];
      }
      break;

    case RoleAbility::NECROMANCER:
      foreach ($result_list as $result) {
	$target = $result['target'];
	self::OutputAbilityResult($header . '_result', $target, $footer . $result['result']);
      }
      break;

    case RoleAbility::PRIEST:
      foreach ($result_list as $result) {
	self::OutputAbilityResult($header, $result['result'], $footer);
      }
      break;

    case RoleAbility::WEATHER_PRIEST:
      foreach ($result_list as $result) {
	self::OutputAbilityResult($header, null, $result['result']);
      }
      break;

    case RoleAbility::CRISIS_PRIEST:
      foreach ($result_list as $result) {
	self::OutputAbilityResult($header . $result['result'], null, $footer);
      }
      break;

    case RoleAbility::REPORTER:
      if ($uniq) $stack = [];
      foreach ($result_list as $result) {
	if ($uniq && in_array($result['result'], $stack)) continue;
	$target = $result['target'] . ' さんは ' . $result['result'];
	self::OutputAbilityResult($header, $target, $footer);
	if ($uniq) $stack[] = $result['result'];
      }
      break;

    case RoleAbility::FOX:
      foreach ($result_list as $result) {
	self::OutputAbilityResult($header . $result['result'], null);
      }
      break;
    }
  }

  //個別能力発動結果表示
  public static function OutputAbilityResult($header, $target, $footer = null) {
    $str = Text::LineFeed(TableHTML::GenerateHeader('ability-result'));
    if (true === isset($header)) {
      $str .= Text::LineFeed(ImageManager::Role()->Generate($header, null, true));
    }
    if (true === isset($target)) {
      $str .= Text::LineFeed(TableHTML::GenerateTd($target));
    }
    if (true === isset($footer)) {
      $str .= Text::LineFeed(ImageManager::Role()->Generate($footer, null, true));
    }
    echo $str;
    TableHTML::OutputFooter();
  }

  //投票のチェックボックスヘッダ取得
  public static function GetVoteCheckboxHeader($type) {
    switch ($type) {
    case OptionFormType::RADIO:
      return '<input type="radio" name="target_no"';

    case OptionFormType::CHECKBOX:
      return '<input type="checkbox" name="target_no[]"';
    }
  }

  //夜の投票済みメッセージを取得
  private static function GetVoteMessage(array $stack, $type, $not_type = '') {
    switch ($type) {
    case VoteAction::WOLF:
    case VoteAction::STEP_WOLF:
    case VoteAction::SILENT_WOLF:
    case VoteAction::CUPID:
    case VoteAction::DUELIST:
      return RoleAbilityMessage::VOTED;

    case VoteAction::STEP_MAGE:
    case VoteAction::STEP_GUARD:
    case VoteAction::STEP_ASSASSIN:
    case VoteAction::STEP_SCAN:
    case VoteAction::SPREAD_WIZARD:
    case VoteAction::STEP_VAMPIRE:
      return self::GetMultiVoteMessage($stack);

    case VoteAction::STEP:
      if ($not_type != '' && $stack['type'] == $not_type) {
	return RoleAbilityMessage::CANCEL_VOTED;
      }
      return self::GetMultiVoteMessage($stack);

    case VoteAction::REVIVE:
    case VoteAction::POSSESSED:
    case VoteAction::GRAVE:
      if ($not_type != '' && $stack['type'] == $not_type) {
	return RoleAbilityMessage::CANCEL_VOTED;
      }
      $user = DB::$USER->ByID($stack['target_no']);
      return $user->handle_name . RoleAbilityMessage::SETTLE_VOTED;

    default:
      if ($not_type != '' && $stack['type'] == $not_type) {
	return RoleAbilityMessage::CANCEL_VOTED;
      }
      $user = DB::$USER->ByVirtual($stack['target_no']);
      return $user->handle_name . RoleAbilityMessage::SETTLE_VOTED;
    }
  }

  //夜の投票済みメッセージを取得 (複数投票型)
  private static function GetMultiVoteMessage(array $stack) {
    $str_stack = [];
    foreach (Text::Parse($stack['target_no']) as $id) {
      $user = DB::$USER->ByVirtual($id);
      $str_stack[$user->id] = $user->handle_name;
    }
    ksort($str_stack);
    $str = RoleAbilityMessage::HONORIFIC . ' ';
    return ArrayFilter::Concat($str_stack, $str) . RoleAbilityMessage::SETTLE_VOTED;
  }
}
