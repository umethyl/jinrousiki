<?php
//-- 特徴と仕様情報コントローラー --//
final class ScriptInfoInfoController extends JinrouController {
  protected static function LoadSetting() {
    Loader::LoadClass('InfoTime');
  }

  protected static function Output() {
    InfoHTML::OutputHeader(ScriptInfoMessage::TITLE, 0, 'script_info');
    InfoHTML::Load('script_info');
    HTML::OutputFooter();
  }

  //身代わり君の配役対象外となる役職グループのリスト出力
  public static function OutputDisableDummyBoyRole() {
    $stack = [];
    foreach (Cast::GetDisableCastDummyBoyRoleBaseList() as $role) {
      $stack[] = RoleDataManager::GetName($role);
    }
    echo ArrayFilter::Concat($stack, ScriptInfoMessage::DOT);
  }

  //自動リロード設定出力
  public static function OutputAutoReload() {
    $delimiter = Message::SECOND . ScriptInfoMessage::DOT;
    echo ArrayFilter::Concat(GameConfig::$auto_reload_list, $delimiter) . Message::SECOND;
  }

  //村の最大人数設定出力
  public static function OutputMaxUser() {
    $delimiter = ScriptInfoMessage::HUMAN . ScriptInfoMessage::DOT;
    printf(ScriptInfoMessage::MAX_USER_HEADER . Text::BR . ScriptInfoMessage::MAX_USER_FOOTER,
      ArrayFilter::Concat(RoomConfig::$max_user_list, $delimiter) . ScriptInfoMessage::HUMAN,
      ArrayFilter::GetMin(CastConfig::$role_list)
    );
  }
}
