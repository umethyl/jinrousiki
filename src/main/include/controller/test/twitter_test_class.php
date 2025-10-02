<?php
//-- Twitter 投稿テストコントローラー --//
final class TwitterTestController extends JinrouController {
  protected static function Load() {
    DevHTML::LoadRequest();
  }

  protected static function Output() {
    HTML::OutputHeader(TwitterMessage::TITLE, null, true);
    TwitterTestHTML::OutputForm();
    if (DevHTML::IsExecute()) {
      self::RunTest();
    }
    HTML::OutputFooter();
  }

  //テスト実行
  private static function RunTest() {
    RQ::Get()->ParsePostInt('number');
    RQ::Get()->ParsePostData('name', 'comment');
    if (JinrouTwitter::Send(RQ::Get()->number, RQ::Get()->name, RQ::Get()->comment)) {
      Text::d(TwitterMessage::SUCCESS);
    }
  }
}
