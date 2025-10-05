<?php
//-- Twitter 投稿テストコントローラー --//
final class TwitterTestController extends JinrouTestController {
  protected static function LoadRequest() {
    DevHTML::LoadRequest();
  }

  protected static function OutputHeader() {
    HTML::OutputHeader(TwitterMessage::TITLE, null, true);
    TwitterTestHTML::OutputForm();
  }

  protected static function IsExecute() {
    return DevHTML::IsExecute();
  }

  protected static function RunTest() {
    RQ::Get()->ParsePostInt('number');
    RQ::Get()->ParsePostData('name', 'comment');
    if (JinrouTwitter::Send(RQ::Get()->number, RQ::Get()->name, RQ::Get()->comment)) {
      Text::d(TwitterMessage::SUCCESS);
    }
  }
}
