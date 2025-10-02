<?php
class VoteTestTalk {
  private static $day = [
    ['uname' => 'moon', 'font_type' => TalkVoice::NORMAL,
     'sentence' => '●かー'],
    ['uname' => 'frame', 'font_type' => TalkVoice::NORMAL,
     'location' => TalkVoice::SECRET,
     'sentence' => 'よしよし、気づかれてないな'],
    ['uname' => 'frame', 'font_type' => TalkVoice::NORMAL,
     'sentence' => 'おっすおっす'],
    ['uname' => 'light_blue', 'font_type' => TalkVoice::WEAK,
     'sentence' => 'えっ'],
    ['uname' => 'green',
     'location' => TalkLocation::SYSTEM, 'action' => TalkAction::OBJECTION],
    ['uname' => 'dark_gray', 'font_type' => TalkVoice::NORMAL,
     'location' => TalkVoice::SECRET,
     'sentence' => 'チラッチラッ'],
    ['uname' => 'dark_gray', 'font_type' => TalkVoice::WEAK,
     'sentence' => 'チラッ'],
    ['uname' => 'yellow', 'font_type' => TalkVoice::STRONG,
     'sentence' => "占いCO\n黒は●"],
    ['uname' => 'light_gray', 'font_type' => TalkVoice::NORMAL,
     'location' => TalkVoice::SECRET,
     'sentence' => 'ねむい'],
    ['uname' => 'light_gray', 'font_type' => TalkVoice::NORMAL,
     'sentence' => 'おはよう'],
    ['uname' => GM::SYSTEM,
     'location' => TalkLocation::SYSTEM, 'action' => TalkAction::MORNING,
     'sentence' => VoteTestRoom::DATE]
  ];

  private static $night = [
    ['uname' => 'cloud',
     'font_type' => TalkVoice::NORMAL, 'sentence' => '吸血鬼なんだ'],
    ['uname' => 'light_blue',
     'font_type' => TalkVoice::WEAK, 'sentence' => 'えっ'],
    ['uname' => 'moon',
     'font_type' => TalkVoice::NORMAL, 'sentence' => 'あーうー'],
    ['uname' => 'gold',
     'location' => TalkLocation::COMMON, 'font_type' => TalkVoice::NORMAL,
     'sentence' => 'やあやあ'],
    ['uname' => 'rose',
     'font_type' => TalkVoice::STRONG, 'sentence' => '誰吸血しようかな'],
    ['uname' => 'frame',
     'font_type' => TalkVoice::NORMAL, 'sentence' => 'どうしよう'],
    ['uname' => 'black',
     'location' => TalkLocation::FOX, 'font_type' => TalkVoice::WEAK,
     'sentence' => '占い師早く死んで欲しいなぁ'],
    ['uname' => 'gust',
     'location' => TalkLocation::SYSTEM, 'action' => VoteAction::NOT_GRAVE],
    ['uname' => 'cherry',
     'location' => TalkLocation::MAD, 'font_type' => TalkVoice::WEAK,
     'sentence' => 'やあ'],
    ['uname' => 'white',
     'location' => TalkLocation::SYSTEM, 'action' => VoteAction::MAGE,
     'sentence' => '黒'],
    ['uname' => 'green',
     'font_type' => TalkVoice::NORMAL, 'sentence' => 'てすてす'],
    ['uname' => 'dark_gray',
     'font_type' => TalkVoice::WEAK, 'sentence' => 'チラッ'],
    ['uname' => 'yellow',
     'font_type' => TalkVoice::STRONG, 'sentence' => "占いCO\n黒は●"],
    ['uname' => 'light_gray',
     'location' => TalkLocation::WOLF, 'font_type' => TalkVoice::NORMAL,
     'sentence' => '生き延びたか'],
    ['uname' => TalkLocation::SYSTEM, 'action' => TalkAction::NIGHT]
  ];

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
      return [];
    }
  }
}
