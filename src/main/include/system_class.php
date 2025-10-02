<?php
//-- システムユーザクラス --//
class GM {
  const ID        = 1;			//ユーザ ID
  const SYSTEM    = 'system';		//システムユーザ
  const DUMMY_BOY = 'dummy_boy';	//身代わり君
  const PROFILE   = 'ゲームマスター';	//プロフィール (初期処理用)
  const ROLE      = 'GM';		//役職 (初期処理用)
}

//-- 汎用スタッククラス --//
class Stack {
  //初期化
  public function Init($name) {
    //Text::p($name, '◆Stack/Init');
    $this->Set($name, array());
  }

  //取得
  public function Get($name) {
    //Text::p($name, '◆Stack/Get[Get]');
    return isset($this->$name) ? $this->$name : null;
  }

  //取得 (配列)
  public function GetKey($name, $key) {
    $stack = $this->GetArray($name);
    return is_null($stack) ? null : ArrayFilter::Get($stack, $key);
  }

  //取得 (array_keys() ベース)
  public function GetKeyList($name, $key = null) {
    return ArrayFilter::GetKeyList($this->GetArray($name, true), $key);
  }

  //セット
  public function Set($name, $data) {
    //Text::p($data, "◆Stack/Set[{$name}]");
    $this->$name = $data;
  }

  //追加
  public function Add($name, $data) {
    //Text::p($data, "◆Stack/Add[{$name}]");
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    $stack[] = $data;
    $this->Set($name, $stack);
  }

  //存在判定
  public function Exists($name) {
    //Text::p($name, '◆Stack/Exists');
    return $this->Count($name) > 0;
  }

  //存在判定 (配列)
  public function ExistsKey($name, $key) {
    //Text::p($name, "◆Stack/Exists[{$key}]");
    return ArrayFilter::Exists($this->Get($name), $key);
  }

  //存在判定 (in_array() ラッパー)
  public function IsInclude($name, $value) {
    //Text::p($name, "◆Stack/IsInclude[{$value}]");
    return ArrayFilter::IsInclude($this->Get($name), $value);
  }

  //未設定判定
  public function IsEmpty($name) {
    return is_null($this->Get($name));
  }

  //カウント
  public function Count($name) {
    return count($this->Get($name));
  }

  //シャッフル
  public function Shuffle($name) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    shuffle($stack);
    $this->Set($name, $stack);
  }

  //削除
  public function Delete($name, $data) {
    //Text::p($data, "◆Stack/Delete[{$name}]");
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    $key = array_search($data, $stack);
    if ($key !== false) $this->DeleteKey($name, $key);
  }

  //削除 (キー指定)
  public function DeleteKey($name, $key) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    unset($stack[$key]);
    $this->Set($name, $stack);
  }

  //削除 (差分指定)
  public function DeleteDiff($name, array $list) {
    $stack = $this->GetArray($name);
    if (is_null($stack)) return;

    $this->Set($name, array_values(array_diff($stack, $list)));
  }

  //消去
  public function Clear($name) {
    //Text::p($name, '◆Stack/Clear');
    unset($this->$name);
  }

  //表示 (デバッグ用)
  public function p($data = null, $name = null) {
    Text::p(isset($data) ? $this->Get($data) : $this, $name);
  }

  //取得 (配列固定)
  private function GetArray($name, $fill = false) {
    return ArrayFilter::Cast($this->Get($name), $fill);
  }
}

//-- フラグ専用スタッククラス --//
class FlagStack extends Stack {
  public function __get($name) {
    //Text::p($name, '◆FlagStack/__get');
    return $this->Off($name);
  }

  public function Set($name, $data) {
    //Text::v($data, $name);
    $this->$name = true === $data;
  }

  //ON
  public function On($name) {
    $this->Set($name, true);
  }

  //OFF
  public function Off($name) {
    $this->Set($name, false);
  }
}

//-- マネージャ基底クラス --//
abstract class StackManager {
  private $stack;
  private $flag;

  //-- スタック関連 --//
  //スタック取得
  final public function Stack() {
    if (is_null($this->stack)) {
      $this->stack = new Stack();
    }
    //if (get_class($this) == 'Room') Text::p($this->stack);
    return $this->stack;
  }

  //フラグスタック取得
  final public function Flag() {
    if (is_null($this->flag)) {
      $this->flag = new FlagStack();
    }
    //if (get_class($this) == 'Room') Text::p($this->flag);
    return $this->flag;
  }

  //フラグセット
  final public function SetFlag() {
    foreach (func_get_args() as $mode) {
      $this->Flag()->On($mode);
    }
  }

  //ON 判定
  final public function IsOn($mode) {
    //if (get_class($this) == 'User') Text::p($this->Flag());
    return $this->Flag()->Get($mode);
  }

  //OFF 判定
  final public function IsOff($mode) {
    return ! $this->IsOn($mode);
  }
}
