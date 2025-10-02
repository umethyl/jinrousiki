<?php
//-- 村・本人の勝敗結果 --//
class WinnerMessage {
  //村人勝利
  static public $human = '[村人勝利] 村人たちは人狼の血を根絶することに成功しました';

  //人狼・狂人勝利
  static public $wolf = '[人狼・狂人勝利] 最後の一人を食い殺すと人狼達は次の獲物を求めて村を後にした';

  //妖狐勝利 (村人勝利版)
  static public $fox1 = '[妖狐勝利] 人狼がいなくなった今、我の敵などもういない';

  //妖狐勝利 (人狼勝利版)
  static public $fox2 = '[妖狐勝利] マヌケな人狼どもを騙すことなど容易いことだ';

  //恋人・キューピッド勝利
  static public $lovers = '[恋人・キューピッド勝利] 愛の前には何者も無力だったのでした';

  //出題者勝利
  static public $quiz = '[出題者勝利] 真の解答者にはまだ遠い……修行あるのみ';

  //出題者死亡
  static public $quiz_dead = '[引き分け] 何という事だ！このままでは決着が付かないぞ！';

  //吸血鬼勝利
  static public $vampire = '[吸血鬼勝利] 夜の支配者に抗える存在など、ありはしない';

  //引き分け
  static public $draw = '[引き分け] 引き分けとなりました';

  //全滅
  static public $vanish = '[引き分け] そして誰も居なくなった……';

  //途中廃村
  static public $unfinished = '[引き分け] 霧が濃くなって何も見えなくなりました……';

  //廃村
  static public $none = '過疎が進行して人がいなくなりました';

  static public $self_win  = 'あなたは勝利しました'; //本人勝利
  static public $self_lose = 'あなたは敗北しました'; //本人敗北
  static public $self_draw = '引き分けとなりました'; //引き分け
}
