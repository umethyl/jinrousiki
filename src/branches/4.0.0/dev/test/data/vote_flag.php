<?php
class VoteTestFlag {
  const VOTE = false; //投票表示モード
  const CAST = false; //配役情報表示モード
  const TALK = false; //発言表示モード
  const ROLE = false; //画像表示モード

  public static $role_list = [
    'main'    => true,
    'sub'     => false,
    'result'  => false,
    'weather' => false
  ];
}
