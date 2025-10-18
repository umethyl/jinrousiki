<?php
//-- 役職名表示コントローラー --//
final class NameTestController extends JinrouTestController {
  protected static function LoadRequest() {
    DevHTML::LoadRequest();
    RQ::Get()->ParsePostData('type');
  }

  protected static function OutputHeader() {
    HTML::OutputHeader(NameTestMessage::TITLE, 'test/name', true);
    self::OutputForm();
  }

  //フォーム出力
  private static function OutputForm() {
    HTML::OutputFormHeader('name_test.php');
    Text::d();
    self::OutputFormList();
    HTML::OutputFormFooter();
  }

  //フォームリスト出力
  private static function OutputFormList() {
    $id        = RQ::Get()->type ?? 'all-all' ;
    $count     = 0;
    $role_data = self::GetList();
    $stack     = ['camp' => VoteMessage::CAMP_FOOTER, 'group' => VoteMessage::GROUP_FOOTER];

    self::OutputRadio('all-all', Message::FORM_ALL, $id);
    foreach ($stack as $type => $name) {
      foreach (array_keys($role_data->$type) as $role) {
	Text::OutputFold(++$count, Text::BR, 9);
	self::OutputRadio($role . '-' . $type, RoleDataManager::GetName($role) . $name, $id);
      }
    }
  }

  //ラジオボタン出力
  private static function OutputRadio($id, $label, $checked_id) {
    DevHTML::OutputRadio($id, 'type', $id, HTML::GenerateChecked($id === $checked_id), $label);
  }

  protected static function IsExecute() {
    return DevHTML::IsExecute();
  }

  protected static function RunTest() {
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

    if (null === $stack) {
      $stack = new stdClass();
      foreach (RoleDataManager::GetList() as $role) {
	$stack->group[RoleDataManager::GetGroup($role)][]     = $role;
	$stack->camp[RoleDataManager::GetCamp($role, true)][] = $role;
      }
    }
    return $stack;
  }
}
