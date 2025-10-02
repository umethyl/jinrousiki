<?php
class VoteTestTalk {
  private static $day = array(
    array('uname' => 'moon',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => '●かー'),
    array('uname' => 'light_blue',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'えっ'),
    array('uname' => 'green',
	  'location' => TalkLocation::SYSTEM, 'action' => TalkAction::OBJECTION),
    array('uname' => 'dark_gray',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'チラッ'),
    array('uname' => 'yellow',
	  'font_type' => TalkVoice::STRONG, 'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'おはよう'),
    array('uname' => GM::SYSTEM,
	  'location' => TalkLocation::SYSTEM, 'action' => TalkAction::MORNING,
	  'sentence' => VoteTestRoom::DATE)
  );

  private static $night = array(
    array('uname' => 'cloud',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => '吸血鬼なんだ'),
    array('uname' => 'light_blue',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'えっ'),
    array('uname' => 'moon',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'あーうー'),
    array('uname' => 'gold',
	  'location' => TalkLocation::COMMON, 'font_type' => TalkVoice::NORMAL,
	  'sentence' => 'やあやあ'),
    array('uname' => 'rose',
	  'font_type' => TalkVoice::STRONG, 'sentence' => '誰吸血しようかな'),
    array('uname' => 'frame',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'どうしよう'),
    array('uname' => 'black',
	  'location' => TalkLocation::FOX, 'font_type' => TalkVoice::WEAK,
	  'sentence' => '占い師早く死んで欲しいなぁ'),
    array('uname' => 'gust',
	  'location' => TalkLocation::SYSTEM, 'action' => VoteAction::NOT_GRAVE),
    array('uname' => 'cherry',
	  'location' => TalkLocation::MAD, 'font_type' => TalkVoice::WEAK,
	  'sentence' => 'やあ'),
    array('uname' => 'white',
	  'location' => TalkLocation::SYSTEM, 'action' => VoteAction::MAGE,
	  'sentence' => '黒'),
    array('uname' => 'green',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'てすてす'),
    array('uname' => 'dark_gray',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'チラッ'),
    array('uname' => 'yellow',
	  'font_type' => TalkVoice::STRONG, 'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray',
	  'location' => TalkLocation::WOLF, 'font_type' => TalkVoice::NORMAL,
	  'sentence' => '生き延びたか'),
    array('uname' => TalkLocation::SYSTEM, 'action' => TalkAction::NIGHT)
  );

  static function Get() {
    switch (DB::$ROOM->scene) {
    case RoomScene::DAY:
      return self::${DB::$ROOM->scene};

    case RoomScene::NIGHT:
      $stack = self::${DB::$ROOM->scene};
      foreach ($stack as &$list) {
	if (! isset($list['location'])) {
	  if ($list['uname'] == GM::SYSTEM) {
	    $list['location'] = TalkLocation::SYSTEM;
	  } else {
	    $list['location'] = TalkLocation::MONOLOGUE;
	  }
	}
      }
      return $stack;

    default:
      return array();
    }
  }
}
