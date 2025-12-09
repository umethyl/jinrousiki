<?php
class VoteTestFlag {
  const VOTE = false; //投票表示
  const CAST = false; //配役情報表示
  const TALK = false; //発言表示
  const ROLE = false; //画像表示
  const MAGE = false; //占い判定
  const NECROMANCER = false; //霊能判定

  //画像表示種別
  public static $role_list = [
    'main'    => true,
    'sub'     => false,
    'result'  => false,
    'weather' => false
  ];
}
