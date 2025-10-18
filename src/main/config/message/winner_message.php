<?php
//-- 勝敗結果 --//
class WinnerMessage {
  /* 村 */
  //村人勝利
  public static $human = '[村人勝利] 村人たちは人狼の血を根絶することに成功しました';

  //村人勝利 (クイズ村勝利版)
  public static $human_quiz = '[村人勝利] 知恵と勇気で全ての難問を解くことに成功しました';

  //人狼・狂人勝利
  public static $wolf = '[人狼・狂人勝利] 最後の一人を食い殺すと人狼達は次の獲物を求めて村を後にした';

  //人狼・狂人勝利 (クイズ村勝利版)
  public static $wolf_quiz = '[人狼・狂人勝利] 知恵を絞ったところで人狼の力の前では無力だ';

  //妖狐勝利 (村人勝利版)
  public static $fox1 = '[妖狐勝利] 人狼がいなくなった今、我の敵などもういない';

  //妖狐勝利 (人狼勝利版)
  public static $fox2 = '[妖狐勝利] マヌケな人狼どもを騙すことなど容易いことだ';

  //妖狐勝利 (クイズ村勝利版)
  public static $fox_quiz = '[妖狐勝利] 妖狐に知恵比べで勝とうなぞ浅ましい';

  //恋人・キューピッド勝利
  public static $lovers = '[恋人・キューピッド勝利] 愛の前には何者も無力だったのでした';

  //出題者勝利
  public static $quiz = '[出題者勝利] 真の解答者にはまだ遠い……修行あるのみ';

  //吸血鬼勝利
  public static $vampire = '[吸血鬼勝利] 夜の支配者に抗える存在など、ありはしない';

  //引き分け
  public static $draw = '[引き分け] 引き分けとなりました';

  //引き分け (出題者死亡)
  public static $quiz_dead = '[引き分け] 何という事だ！このままでは決着が付かないぞ！';

  //引き分け (全滅)
  public static $vanish = '[引き分け] そして誰も居なくなった……';

  //引き分け (途中廃村)
  public static $unfinished = '[引き分け] 霧が濃くなって何も見えなくなりました……';

  //廃村
  public static $none = '過疎が進行して人がいなくなりました';

  /* 本人 */
  public static $self_win  = 'あなたは勝利しました';
  public static $self_lose = 'あなたは敗北しました';
  public static $self_draw = '引き分けとなりました';

  /* 個人 */
  public static $personal_win  = '勝利';
  public static $personal_lose = '敗北';
  public static $personal_draw = '引分';
  public static $personal_none = '不明';

  /* 画像 */
  public static $image_human   = '村人勝利';
  public static $image_wolf    = '人狼勝利';
  public static $image_fox     = '妖狐勝利';
  public static $image_lovers  = '恋人勝利';
  public static $image_quiz    = '出題者勝利';
  public static $image_vampire = '吸血鬼勝利';
  public static $image_draw    = '引き分け';
}
