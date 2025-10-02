<?php
/*
  ◆猩々 (clairvoyance_scanner)
  ○仕様
  ・追加役職：なし
  ・投票結果：透視
  ・投票：2日目以降
*/
RoleManager::LoadFile('mind_scanner');
class Role_clairvoyance_scanner extends Role_mind_scanner{
  public $mind_role = NULL;
  public $result = 'CLAIRVOYANCE_RESULT';
  public $ignore_message = '初日は透視できません';
  function __construct(){ parent::__construct(); }

  protected function OutputResult(){
    global $ROOM;
    if($ROOM->date > 2) OutputSelfAbilityResult($this->result);
  }

  function IsVote(){ global $ROOM; return $ROOM->date > 1; }

  /*
    複数の投票イベントを持つタイプが出現した場合は複数のメッセージを発行する必要がある
    対象が NULL でも有効になるタイプ (キャンセル投票はスキップ) は想定していない
  */
  function Report($user){
    global $ROOM, $USERS;

    foreach($this->GetStack('vote_data') as $action => $vote_stack){
      if(strpos($action, '_NOT_DO') !== false ||
	 ! array_key_exists($user->uname, $vote_stack)) continue;
      $str = $this->GetActor()->GetHandleName($user->uname) . "\t";
      $target_stack = $vote_stack[$user->uname];

      if($user->IsRole('barrier_wizard')){
	$str_stack = array();
	foreach(explode(' ', $target_stack) as $id){
	  $voted_user = $USERS->ByVirtual($id);
	  $str_stack[$voted_user->user_no] = $str . $voted_user->handle_name;
	}
	ksort($str_stack);
	foreach($str_stack as $str) $ROOM->SystemMessage($str, $this->result);
      }
      else{
	$ROOM->SystemMessage($str . $USERS->GetHandleName($target_stack, true), $this->result);
      }
    }
  }
}
