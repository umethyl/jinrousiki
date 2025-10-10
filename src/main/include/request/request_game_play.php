<?php
/*
  ◆game play 用共通クラス (GamePlay)
  ○仕様
*/
RQ::LoadFile('request_game');
class RequestGamePlay extends RequestGame {
  private $url_stack = [];

  public function __construct() {
    parent::__construct();
    $this->ParseGetOn(
      RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::NAME, RequestDataGame::DOWN,
      RequestDataGame::WORDS
    );
    $this->ParsePostData('token');
    if (GameConfig::ASYNC) {
      $this->ParseGetOn(RequestDataGame::ASYNC);
    } else {
      $this->async = false;
    }
  }

  protected function GetURL($auto_reload = false) {
    $url = '?room_no=' . $this->room_no;
    if ($auto_reload || $this->auto_reload > 0) {
      $url .= URL::GetReload($this->auto_reload);
    }

    $stack = [
      RequestDataGame::SOUND, RequestDataGame::ICON, RequestDataGame::NAME, RequestDataGame::DOWN,
      RequestDataGame::WORDS
    ];
    if (GameConfig::ASYNC) {
      $stack[] = RequestDataGame::ASYNC;
    }
    foreach ($stack as $key) {
      $url .= $this->ToURL($key);
    }

    return $url;
  }

  /** ON/OFF値のクエリパラメータをスタックに追加します。 */
  public function StackOnParam($name, $emptyIfOff = true) {
    $this->StackOnValue($name, $this->$name, $emptyIfOff);
  }

  /** bool型の値をクエリパラメータとしてスタックに追加します。 */
  public function StackOnValue($name, $value, $emptyIfOff = true) {
    $value = Switcher::Get($value);
    if (! $emptyIfOff || Switcher::IsOn($value)) {
      $this->url_stack[$name] = $value;
    }  else {
      $this->url_stack[$name] = '';
    }
  }

  /** 整数値のクエリパラメータをスタックに追加します。 */
  public function StackIntParam($name, $emptyIfZero = true) {
    $value = intval($this->$name);
    if (! $emptyIfZero || $value != 0) {
      $this->url_stack[$name] = $value;
    } else {
      $this->url_stack[$name] = '';
    }
  }

  /** 文字列型のクエリパラメータをスタックに追加します。 */
  public function StackRawParam($name) {
    $this->url_stack[$name] = $this->$name;
  }

  /** スタックされたクエリパラメータを直接取得します。 */
  public function GetRawUrlStack(array $except = null, array $filter = null) {
    if (empty($except)) {
      $diff = $this->url_stack;
    } else {
      $diff = array_diff_key($this->url_stack, array_flip($except));
    }

    return empty($filter) ? $diff : array_intersect_key($diff, array_flip($filter));
  }

  /** スタックされたクエリパラメータを結合して取得します。 */
  public function GenerateUrl(array $except = null, array $filter = null) {
    $query = '';
    foreach ($this->GetRawUrlStack($except, $filter) as $name => $value) {
      if ($value != '') {
	$format = ('' === $query) ? '%s=%s' : '&%s=%s';
	$query .= sprintf($format, $name, urlencode($value));
      }
    }
    return $query;
  }
}
