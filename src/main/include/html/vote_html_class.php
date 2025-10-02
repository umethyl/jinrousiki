<?php
//-- HTML 生成クラス (投票拡張) --//
class VoteHTML {
  //結果出力
  public static function OutputResult($str, $reset = false) {
    if ($reset) RoomDB::DeleteVote(); //今までの投票を全部削除
    HTML::OutputResult(ServerConfig::TITLE . VoteMessage::RESULT, self::GenerateResult($str));
  }

  //エラーページ出力
  public static function OutputError($title, $str = null) {
    if (is_null($str)) {
      $str = VoteMessage::BUG;
    }
    HTML::OutputResult(sprintf(VoteMessage::ERROR_TITLE, $title), self::GenerateResult($str));
  }

  //開始前の投票ページ出力
  public static function OutputBeforeGame() {
    self::CheckScene(); //投票する状況があっているかチェック
    self::OutputHeader();
    Text::Printf(self::GetBeforeHeader(), VoteAction::KICK);

    $format = self::GetCheckbox();
    $count  = 0;
    $path   = Icon::GetPath();
    foreach (DB::$USER->Get() as $id => $user) {
      TableHTML::OutputFold($count++);
      if (! $user->IsDummyBoy() && (GameConfig::SELF_KICK || ! $user->IsSelf())) {
	$checkbox = sprintf($format, $id, $id);
      } else {
	$checkbox = '';
      }
      ImageHTML::OutputVoteIcon($user, $path . $user->icon_filename, $checkbox);
    }

    Text::Printf(self::GetBeforeFooter(),
      sprintf(VoteMessage::CAUTION_KICK, GameConfig::KICK),
      RQ::Get()->back_url, VoteMessage::KICK_DO, RQ::Get()->post_url,
      Security::GetHash(DB::$ROOM->id), VoteAction::GAME_START, VoteMessage::GAME_START
    );
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //昼の投票ページを出力する
  public static function OutputDay() {
    self::CheckScene(); //投票シーンチェック
    if (DB::$ROOM->IsDate(1)) self::OutputResult(VoteMessage::NEEDLESS_VOTE);
    if (! DB::$ROOM->IsTest() && UserDB::IsVoteKill()) { //投票済みチェック
      self::OutputResult(VoteMessage::ALREADY_VOTE);
    }

    //特殊イベントを参照して投票対象をセット
    if (DB::$ROOM->IsEvent('vote_duel')) {
      $user_stack = array();
      foreach (DB::$ROOM->Stack()->Get('vote_duel') as $id) {
	$user_stack[$id] = DB::$USER->ByID($id);
      }
    } else {
      $user_stack = DB::$USER->Get();
    }
    $virtual_self = DB::$SELF->GetVirtual(); //仮想投票者を取得

    self::OutputHeader();
    Text::Printf(self::GetDayHeader(), VoteAction::VOTE_KILL, DB::$ROOM->revote_count);

    $format = self::GetCheckbox();
    $count  = 0;
    $base_path = Icon::GetPath();
    $dead_icon = Icon::GetDead();
    foreach ($user_stack as $id => $user) {
      TableHTML::OutputFold($count++);
      $is_live = DB::$USER->IsVirtualLive($id);

      //生きていればユーザアイコン、死んでれば死亡アイコン
      $path = $is_live ? $base_path . $user->icon_filename : $dead_icon;
      if ($is_live && ! $user->IsSame($virtual_self)) {
	$checkbox = sprintf($format, $id, $id);
      } else {
	$checkbox = '';
      }
      ImageHTML::OutputVoteIcon($user, $path, $checkbox);
    }

    Text::Printf(self::GetDayFooter(),
      VoteMessage::CAUTION, RQ::Get()->back_url, VoteMessage::VOTE_DO
    );
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //夜の投票ページを出力する
  public static function OutputNight() {
    self::CheckScene(); //投票シーンチェック
    //-- 投票済みチェック --//
    $filter = VoteNight::GetFilter();
    if (! DB::$ROOM->IsTest()) {
      $action     = RoleManager::Stack()->Get('action');
      $not_action = RoleManager::Stack()->Get('not_action');
      VoteNight::CheckVote($action, $not_action);
    }

    self::OutputHeader();
    //Text::p($filter, '◆Filter');
    //RoleManager::Stack()->p();
    TableHTML::OutputHeader('vote-page');
    $count = 0;
    foreach ($filter->GetVoteTargetUser() as $id => $user) {
      TableHTML::OutputFold($count++);
      $live = DB::$USER->IsVirtualLive($id);
      /*
	死者は死亡アイコン (蘇生能力者は死亡アイコンにしない)
	生存者はユーザアイコン (狼仲間なら狼アイコン)
      */
      $path     = $filter->GetVoteIconPath($user, $live);
      $checkbox = $filter->GetVoteCheckbox($user, $id, $live);
      ImageHTML::OutputVoteIcon($user, $path, $checkbox);
    }

    Text::Printf(self::GetNightFooter(),
      VoteMessage::CAUTION, RQ::Get()->back_url,
      RoleManager::Stack()->Get('action'), self::GetSubmit('submit', 'action')
    );

    if (RoleManager::Stack()->Exists('add_action')) {
      Text::Printf(self::GetNightAddAction(), self::GetSubmit('add_submit', 'add_action'));
    } else {
      HTML::OutputFormFooter();
    }

    if (RoleManager::Stack()->Exists('not_action')) {
      Text::Printf(self::GetNightNotAction(),
	RQ::Get()->post_url, Security::GetHash(DB::$ROOM->id),
	RoleManager::Stack()->Get('not_action'), DB::$SELF->id,
	self::GetSubmit('not_submit', 'not_action')
      );
    }

    echo TableHTML::GenerateFooter();
    HTML::OutputDivFooter();
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //死者の投票ページ出力
  public static function OutputHeaven() {
    //投票済みチェック
    if (DB::$SELF->IsDrop())     self::OutputResult(VoteMessage::ALREADY_DROP);
    if (DB::$ROOM->IsOpenCast()) self::OutputResult(VoteMessage::ALREADY_OPEN);

    self::OutputHeader();
    Text::Printf(self::GetHeaven(),
      VoteAction::HEAVEN, VoteMessage::CAUTION, RQ::Get()->back_url, VoteMessage::REVIVE_REFUSE
    );
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //身代わり君 (霊界) の投票ページ出力
  public static function OutputDummyBoy() {
    self::OutputHeader();
    Text::Printf(self::GetDummyBoy(),
      VoteMessage::CAUTION, RQ::Get()->back_url, VoteAction::RESET_TIME, VoteMessage::RESET_TIME
    );

    //蘇生辞退ボタン表示判定
    if (! DB::$SELF->IsDrop() && DB::$ROOM->IsOption('not_open_cast') &&
	! DB::$ROOM->IsOpenCast()) {
      Text::Printf(self::GetDummyBoyReviveRefuse(),
	RQ::Get()->post_url, Security::GetHash(DB::$ROOM->id),
	VoteAction::HEAVEN, VoteMessage::REVIVE_REFUSE
      );
    }

    echo TableHTML::GenerateFooter();
    HTML::OutputDivFooter();
    if (! DB::$ROOM->IsTest()) HTML::OutputFooter(true);
  }

  //シーンの一致チェック
  private static function CheckScene() {
    if (! DB::$SELF->CheckScene()) self::OutputResult(VoteMessage::RELOAD);
  }

  //結果生成
  private static function GenerateResult($str) {
    return sprintf(self::GetResult(), $str, Text::BR, RQ::Get()->back_url);
  }

  //ヘッダ出力
  private static function OutputHeader() {
    $css = empty(DB::$ROOM->scene) ? null : sprintf('%s/game_%s', JINROU_CSS, DB::$ROOM->scene);

    HTML::OutputHeader(ServerConfig::TITLE . VoteMessage::TITLE, 'game');
    HTML::OutputCSS(sprintf('%s/game_vote', JINROU_CSS));
    GameHTML::OutputSceneCSS();
    HTML::OutputBodyHeader($css);
    GameHTML::OutputGameTop();
    Text::Printf(self::GetHeader(), RQ::Get()->post_url, Security::GetHash(DB::$ROOM->id));
  }

  //夜の投票ボタンメッセージ取得
  private static function GetSubmit($submit, $action) {
    if (RoleManager::Stack()->IsEmpty($submit)) {
      RoleManager::Stack()->Set($submit, RoleManager::Stack()->Get($action));
    }
    $str = RoleManager::Stack()->Get($submit);
    return VoteRoleMessage::$$str;
  }

  //ヘッダタグ
  private static function GetHeader() {
    return <<<EOF
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="hash" value="%s">
EOF;
  }

  //結果タグ
  private static function GetResult() {
    return <<<EOF
<div id="game_top" align="center">%s%s
%s
</div>
EOF;
  }

  //投票画面チェックボックスタグ
  private static function GetCheckbox() {
    return '<input type="radio" name="target_no" id="%d" value="%d">';
  }

  //開始前の投票画面ヘッダタグ
  private static function GetBeforeHeader() {
    return <<<EOF
<input type="hidden" name="situation" value="%s">
<table class="vote-page"><tr>
EOF;
  }

  //開始前の投票画面フッタタグ
  private static function GetBeforeFooter() {
    return <<<EOF
</tr></table>
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td class="add-action"><input type="submit" value="%s"></form></td>
<td>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="hash" value="%s">
<input type="hidden" name="situation" value="%s">
<input type="submit" value="%s">
</form>
</td>
</tr></table></div>
EOF;
  }

  //昼の投票画面ヘッダタグ
  private static function GetDayHeader() {
    return <<<EOF
<input type="hidden" name="situation" value="%s">
<input type="hidden" name="revote_count" value="%d">
<table class="vote-page"><tr>
EOF;
  }

  //昼の投票画面フッタタグ
  private static function GetDayFooter() {
    return <<<EOF
</tr></table>
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td><input type="submit" value="%s"></td>
</tr></table></div>
</form>
EOF;
  }

  //夜の投票画面フッタタグ
  private static function GetNightFooter() {
    return <<<EOF
</tr></table>
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<input type="hidden" name="situation" value="%s">
<td><input type="submit" value="%s"></td>
EOF;
  }

  //夜の投票画面追加能力タグ
  private static function GetNightAddAction() {
    return <<<EOF
<td class="add-action">
<input type="checkbox" name="add_action" id="add_action" value="on">
<label for="add_action">%s</label>
</td>
</form>
EOF;
  }

  //夜の投票画面キャンセルタグ
  private static function GetNightNotAction() {
    return <<<EOF
<td class="add-action">
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="hash" value="%s">
<input type="hidden" name="situation" value="%s">
<input type="hidden" name="target_no" value="%d">
<input type="submit" value="%s"></form>
</td>
EOF;
  }

  //死者の投票画面タグ
  private static function GetHeaven() {
    return <<<EOF
<input type="hidden" name="situation" value="%s">
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td><input type="submit" value="%s"></td>
</tr></table></div>
</form>
EOF;
  }

  //身代わり君 (霊界) の投票画面タグ
  private static function GetDummyBoy() {
    return <<<EOF
<span class="vote-message">%s</span>
<div class="vote-page-link" align="right"><table><tr>
<td>%s</td>
<td>
<input type="hidden" name="situation" value="%s">
<input type="submit" value="%s">
</form>
</td>
EOF;
  }

  //身代わり君 (霊界) の蘇生辞退投票画面タグ
  private static function GetDummyBoyReviveRefuse() {
    return <<<EOF
<td>
<form method="post" action="%s">
<input type="hidden" name="vote" value="on">
<input type="hidden" name="hash" value="%s">
<input type="hidden" name="situation" value="%s">
<input type="submit" value="%s">
</form>
</td>
EOF;
  }
}
