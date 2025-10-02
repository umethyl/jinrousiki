<?php
//-- オプションメッセージ --//
class OptionMessage {
  //-- ◆ 文字化け抑制 --//

  /* カテゴリ */
  static $category_base      = '基本オプション';
  static $category_dummy_boy = '身代わり君設定';
  static $category_open_cast = '霊界公開設定';
  static $category_add_role  = '追加役職設定';
  static $category_special   = '特殊設定';

  /* 表示制御リンク */
  const TOGGLE_ON  = '展開する';
  const TOGGLE_OFF = '折り畳む';

  /* リアルタイム制 */
  const REALTIME_DAY   = '昼';
  const REALTIME_NIGHT = '夜';

  /* 画像表示 */
  const GAME_OPTION = 'ゲームオプション';
}
