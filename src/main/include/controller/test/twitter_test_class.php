<?php
//-- ◆文字化け抑制◆ --//
//-- Twitter 投稿テストコントローラー --//
final class TwitterTestController extends JinrouController {
  protected static function IsTest() {
    return true;
  }

  protected static function LoadRequestExtra() {
    DevHTML::LoadRequest();
  }

  protected static function OutputRunHeader() {
    HTML::OutputHeader(TwitterMessage::TITLE, null, true);
    TwitterTestHTML::OutputForm();
  }

  protected static function EnableCommand() {
    return DevHTML::IsExecute();
  }

  protected static function RunCommand() {
    RQ::Fetch()->ParsePostInt('number');
    RQ::Fetch()->ParsePostData('name', 'comment');
    if (JinrouTwitter::Send(RQ::Fetch()->number, RQ::Fetch()->name, RQ::Fetch()->comment)) {
      Text::d(TwitterMessage::SUCCESS);
    }
  }

  protected static function OutputRunFooter() {
    HTML::OutputFooter();
  }
}
