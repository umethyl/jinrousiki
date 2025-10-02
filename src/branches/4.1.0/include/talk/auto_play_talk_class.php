<?php
//-- 発言処理クラス (AutoPlay 拡張) --//
final class AutoPlayTalk {
  /* フラグ */
  const ID	= 'talk_id';	//ID
  const DATE	= 'date';	//日数
  const TIME	= 'time';	//時刻
  const SCENE	= 'scene';	//シーン

  //スタック取得
  public static function Stack() {
    static $stack;

    if (null === $stack) {
      $stack = new Stack();
    }
    return $stack;
  }

  //判定用変数初期化
  public static function InitStack() {
    self::Stack()->Set(self::ID, 0);
    self::Stack()->Set(self::TIME, 0);
    self::Stack()->Init(self::SCENE);
  }

  //シーン初期化
  public static function InitScene() {
    self::SetScene();
    if (! self::Stack()->IsInclude(self::SCENE, self::Stack()->Get(self::DATE))) {
      self::Stack()->Add(self::SCENE, self::Stack()->Get(self::DATE));
    }
  }

  //シーンセット
  public static function SetScene($strict = false) {
    self::Stack()->Set(self::DATE, self::GetScene($strict));
    if (self::Stack()->IsEmpty(self::Stack()->Get(self::DATE))) {
      self::Stack()->Init(self::Stack()->Get(self::DATE));
    }
  }

  //IDセット
  public static function GetTalkID(TalkParser $talk) {
    $key  = self::Stack()->Get(self::DATE);
    $id   = self::Stack()->Get(self::ID);
    $time = isset($talk->time_stamp) ? $talk->time_stamp : $talk->time;
    self::Stack()->Set(self::ID, ++$id);
    self::Stack()->Set(self::TIME, $time);
    $stack = self::Stack()->Get($key);
    $stack[$id] = $time;
    self::Stack()->Set($key, $stack);
    return sprintf(' id="talk_%d"', $id);
  }

  //データ隠蔽
  public static function Hide($str, $class) {
    $scene  = self::Stack()->Get(self::DATE);
    $header = Text::LineFeed(HTML::GenerateDivHeader('hide', $class . '_' . $scene));
    return $header . $str . HTML::GenerateDivFooter(true);
  }

  //ヘッダ生成
  public static function GenerateHeader($title) {
    $str  = HTML::GenerateHeader($title, 'old_log');
    $str .= HTML::LoadCSS(JINROU_CSS . '/old_log_auto_play');
    $str .= HTML::LoadJavaScript('auto_play');
    $str .= HTML::GenerateBodyHeader();
    return $str;
  }

  //フッタ生成
  public static function GenerateFooter() {
    //self::Stack()->p(null, '◆GenerateFooter');
    $count = 0;
    $speed = RQ::Get()->scroll_time < 1 ? 1 : RQ::Get()->scroll_time;
    $scene_stack = [];
    $talk_stack  = [];
    foreach (array_reverse(self::Stack()->Get(self::SCENE)) as $scene) {
      $scene_stack[] = sprintf("'%s'", $scene);
      foreach ([$scene . '_' . RoomScene::DAY, $scene] as $strict_scene) {
	if (! self::Stack()->Exists($strict_scene)) {
	  continue;
	}

	$stack = self::Stack()->Get($strict_scene);
	$time_stack = [];
	foreach (array_reverse($stack) as $key => $time) {
	  $delay = $time - self::Stack()->Get(self::TIME);
	  if ($count < 1) {
	    self::Stack()->Set(self::TIME, $time);
	    $count++;
	  } else {
	    $delay = floor($delay * 1000 / $speed);
	    array_unshift($time_stack, $delay);
	    self::Stack()->Set(self::TIME, $time);
	  }
	}
	$talk_stack[] = sprintf(self::GetJavaScriptStack(),
	  $strict_scene, ArrayFilter::ConcatKey($stack, ','),
	  $strict_scene, ArrayFilter::ToCSV($time_stack)
	);
      }
    }
    $str  = HTML::GenerateJavaScriptHeader();
    $str .= Text::Format(self::GetJavaScriptHeader(),
      ArrayFilter::ConcatReverse($scene_stack, ',')
    );
    $str .= Text::LineFeed(ArrayFilter::Concat($talk_stack, Text::LF));
    $str .= HTML::GenerateJavaScriptFooter();
    return $str;
  }

  //シーン取得
  private static function GetScene($strict = false) {
    if (! DB::$ROOM->IsPlaying()) {
      return DB::$ROOM->scene;
    } elseif (DB::$ROOM->date > DB::$ROOM->last_date) {
      return RoomScene::AFTER;
    } else {
      return 'date' . DB::$ROOM->date . ($strict ? ('_' . DB::$ROOM->scene) : '');
    }
  }

  //JavaScript 変数取得 (ヘッダ用)
  private static function GetJavaScriptHeader() {
    return <<<EOF
var scene_list     = new Array(%s);
var talk_id_list   = new Array();
var talk_time_list = new Array();
EOF;
  }

  //JavaScript 変数取得 (talk 用)
  private static function GetJavaScriptStack() {
    return <<<EOF
talk_id_list['%s']   = new Array(%s);
talk_time_list['%s'] = new Array(%s);
EOF;
  }
}
