<?php
//-- Twitter 投稿テスト --//
class TwitterTest {
  //実行
  public static function Execute() {
    self::Load();
    self::Output();
  }

  //データロード
  private static function Load() {
    DevHTML::LoadRequest();
  }

  //出力
  private static function Output() {
    HTML::OutputHeader(TwitterMessage::TITLE, null, true);
    TwitterTestHTML::OutputForm();
    if (DevHTML::IsExecute()) self::RunTest();
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
