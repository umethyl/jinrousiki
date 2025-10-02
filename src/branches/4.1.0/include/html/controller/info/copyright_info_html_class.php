<?php
//-- HTML 生成クラス (CopyrightInfo 拡張) --//
class CopyrightInfoHTML {
  //出力
  public static function Output() {
    InfoHTML::OutputHeader(CopyrightInfoMessage::TITLE, 0, 'copyright');
    self::OutputCopyright();
    HTML::OutputFooter();
  }

  //謝辞・素材情報出力
  private static function OutputCopyright() {
    $stack = CopyrightConfig::$list;
    foreach (CopyrightConfig::$add_list as $class => $list) {
      $stack[$class] = isset($stack[$class]) ? array_merge($stack[$class], $list) : $list;
    }

    foreach ($stack as $class => $list) {
      $str = '';
      foreach ($list as $name => $url) {
	$str .= Text::Format(self::GetLink(), $url, $name);
      }
      Text::Printf(self::GetList(), $class, $str);
    }

    Text::Printf(self::GetCopyright(),
      CopyrightInfoMessage::PACKAGE, PHP_VERSION,
      ScriptInfo::PACKAGE, ScriptInfo::VERSION, ScriptInfo::REVISION,
      ScriptInfo::LAST_UPDATE
    );
  }

  //リストタグ
  private static function GetList() {
    return <<<EOF
<h2>%s</h2>
<ul>
%s</ul>
EOF;
  }

  //リンクタグ
  private static function GetLink() {
    return '<li><a target="_blank" href="%s">%s</a></li>';
  }

  //バージョン情報タグ
  private static function GetCopyright() {
    return <<<EOF
<h2>%s</h2>
<ul>
<li>PHP Ver. %s</li>
<li>%s %s (Rev. %d)</li>
<li>LastUpdate: %s</li>
</ul>
EOF;
  }
}
