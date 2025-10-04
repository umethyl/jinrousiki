<?php
//-- 管理用ツールクラス --//
final class JinrouAdmin {
  //有効判定
  public static function Enable(string $type) {
    //共通設定
    if (true !== AdminConfig::ENABLE) {
      return false;
    }

    //個別設定
    $name = $type . '_' . 'enable';
    if (true !== AdminConfig::$$name) {
      return false;
    }
    return true;
  }

  //警告メッセージ出力
  public static function OutputNoticeMessage() {
    //共通設定
    if (true !== AdminConfig::ENABLE) {
      return;
    }

    //stack 初期化
    $stack = new Stack();
    $stack->display = false;
    $stack->Init('type');

    //個別の管理機能有効判定
    $admin_list = ['setup', 'room_delete', 'icon_delete', 'log_delete', 'generate_html_log'];
    foreach ($admin_list as $type) {
      //有効判定
      $name = $type . '_' . 'enable';
      if (true !== AdminConfig::$$name) {
	continue;
      }

      //警告表示判定
      $name = 'notice' . '_' . $type;
      if (true !== AdminConfig::$$name) {
	continue;
      }
      $stack->display = true;
      $stack->Add('type', $type);
    }

    if (false === $stack->display) {
      return;
    }

    HTML::OutputFieldsetHeader(TopPageMessage::NOTICE);
    HTML::OutputDivHeader('information');
    Text::p(AdminMessage::EXPLAIN . Text::BRLF);
    foreach ($stack->Get('type') as $type) {
      Text::p(AdminMessage::$$type . AdminMessage::NOTICE_ENABLE);
    }
    HTML::OutputDivFooter();
    HTML::OutputFieldsetFooter();
  }
}

//-- 管理用コントローラー基底クラス --//
abstract class JinrouAdminController extends JinrouController {
  protected static function Start() {
    if (true !== JinrouAdmin::Enable(static::GetAdminType())) {
      HTML::OutputUnusableError();
    }
  }

  //管理ツール名取得
  abstract protected static function GetAdminType();
}
