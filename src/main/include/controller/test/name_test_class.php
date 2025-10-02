<?php
//-- 役職名表示コントローラー --//
final class NameTestController extends JinrouController {
  protected static function LoadRequest() {
    DevHTML::LoadRequest();
  }

  protected static function Output() {
    HTML::OutputHeader(NameTestMessage::TITLE, 'test/name', true);
    self::OutputForm();
    if (DevHTML::IsExecute()) {
      self::RunTest();
    }
    HTML::OutputFooter();
  }

  //フォーム出力
  private static function OutputForm() {
    HTML::OutputFormHeader('name_test.php');
    Text::d();
    self::OutputRadio('all-all', Message::FORM_ALL);
    $count     = 0;
    $role_data = self::GetList();
    $stack     = ['camp' => VoteMessage::CAMP_FOOTER, 'group' => VoteMessage::GROUP_FOOTER];
    foreach ($stack as $type => $name) {
      foreach (array_keys($role_data->$type) as $role) {
	Text::OutputFold(++$count, Text::BR, 9);
	self::OutputRadio($role . '-' . $type, RoleDataManager::GetName($role) . $name);
      }
    }
    HTML::OutputFormFooter();
  }

  //ラジオボタン出力
  private static function OutputRadio($id, $label) {
    DevHTML::OutputRadio($id, 'type', $id, HTML::GenerateChecked(true), $label);
  }

  //テスト実行
  private static function RunTest() {
    RQ::Get()->ParsePostData('type');
    list($role, $type) = Text::Parse(RQ::Get()->type, '-');
    switch ($type) {
    case 'all':
      $stack = RoleDataManager::GetList();
      break;

    case 'camp':
    case 'group':
      $stack = self::GetList()->{$type}[$role];
      break;

    default:
      return;
    }
    foreach ($stack as $role) {
      Text::d(RoleDataHTML::GenerateMain($role));
    }
  }

  //役職リスト取得
  private static function GetList() {
    static $stack;
    if (is_null($stack)) {
      $stack = new stdClass();
      foreach (RoleDataManager::GetList() as $role) {
	$stack->group[RoleDataManager::GetGroup($role)][]     = $role;
	$stack->camp[RoleDataManager::GetCamp($role, true)][] = $role;
      }
    }
    return $stack;
  }
}
