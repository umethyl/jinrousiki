<?php
//-- 役職名表示コントローラー --//
final class NameTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function LoadRequestExtra() {
    DevHTML::LoadRequest();
    RQ::Fetch()->ParsePostData('type');
  }

  protected static function OutputRunHeader() {
    HTML::OutputHeader(NameTestMessage::TITLE, 'test/name', true);
    self::OutputForm();
  }

  //フォーム出力
  private static function OutputForm() {
    FormHTML::OutputExecute('name_test.php');
    Text::d();
    self::OutputFormList();
    FormHTML::OutputFooter();
  }

  //フォームリスト出力
  private static function OutputFormList() {
    $id        = RQ::Fetch()->type ?? 'all-all' ;
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
    DevHTML::OutputRadio($id, 'type', $id, FormHTML::Checked($id === $checked_id), $label);
  }

  protected static function EnableCommand() {
    return DevHTML::IsExecute();
  }

  protected static function RunCommand() {
    list($role, $type) = Text::Parse(RQ::Fetch()->type, '-');
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

  protected static function OutputRunFooter() {
    HTML::OutputFooter();
  }
}
