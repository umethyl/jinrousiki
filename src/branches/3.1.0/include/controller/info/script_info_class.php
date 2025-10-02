<?php
//-- 特徴と仕様出力クラス --//
class ScriptInfoInfo {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    Loader::LoadClass('InfoTime');
  }

  //出力
  private static function Output() {
    InfoHTML::OutputHeader(ScriptInfoMessage::TITLE, 0, 'script_info');
    InfoHTML::Load('script_info');
    HTML::OutputFooter();
  }

  //身代わり君がなれない役職のリスト出力
  public static function OutputDisableDummyBoyRole() {
    $stack = array();
    foreach (array_merge(array('wolf', 'fox'), CastConfig::$disable_dummy_boy_role_list) as $role) {
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
