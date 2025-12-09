<?php
//-- 異議ありテスト専用メッセージ --//
class ObjectionTestMessage {
  const TITLE = '異議ありテスト';
  const RESET = 'リセット';

  public static $entry			= '入村';
  public static $full			= '定員';
  public static $morning		= '夜明け';
  public static $night			= '日没(遠吠え)';
  public static $vote_success		= '投票完了';
  public static $revote			= '再投票';
  public static $novote			= '未投票告知';
  public static $alert			= '未投票警告';
  public static $objection_male		= '異議あり(男)';
  public static $objection_female	= '異議あり(女)';
}
