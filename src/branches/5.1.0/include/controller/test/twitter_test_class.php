<?php
//-- ◆文字化け抑制◆ --//
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
    RQ::Fetch()->ParsePostInt('number');
    RQ::Fetch()->ParsePostData('name', 'comment');
    if (JinrouTwitter::Send(RQ::Fetch()->number, RQ::Fetch()->name, RQ::Fetch()->comment)) {
      Text::d(TwitterMessage::SUCCESS);
    }
  }
}
