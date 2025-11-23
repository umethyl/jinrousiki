<?php
//-- HTML 生成クラス (GameOptionInfo 拡張) --//
final class GameOptionInfoHTML {
  //出力
  public static function Output() {
    InfoHTML::Output(GameOptionInfoMessage::TITLE, 'game_option');
  }

  //追加役職の説明出力
  public static function OutputAddRole($role) {
    OptionManager::OutputExplain($role);
    printf(GameOptionInfoMessage::ADD_ROLE, CastConfig::$$role);
  }

  //村人置換系オプションのサーバ設定出力
  public static function OutputReplaceRole($option) {
    $format  = GameOptionInfoMessage::REPLACE_ROLE_HEADER . Text::BR;
    $format .= GameOptionInfoMessage::REPLACE_ROLE_FOOTER;
    printf($format, RoleDataHTML::GenerateLink(CastConfig::$replace_role_list[$option]));
  }

  //お祭り村の配役リスト出力
  public static function OutputFestival() {
    $stack  = CastConfig::$festival_role_list;
    $format = '%' . strlen(ArrayFilter::GetMax($stack)) . 's%s%s';
    $str    = '';
    ksort($stack); //人数順に並び替え
    foreach ($stack as $count => $list) {
      $order_stack = [];
      foreach (RoleDataManager::Sort(array_keys($list)) as $role) { //役職順に並び替え
	$order_stack[] = RoleDataManager::GetName($role) . $list[$role];
      }
      $str .= Text::Format($format,
	$count, GameOptionInfoMessage::FESTIVAL_DELIMITER,
	ArrayFilter::Concat($order_stack, Message::SPACER)
      );
    }
    echo $str;
  }
}
