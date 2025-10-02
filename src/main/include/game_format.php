<?php
//-- 発言処理の基底クラス --//
class DocumentBuilder{
  var $actor;
  var $flag;
  var $extension_list = array();

  function DocumentBuilder(){ $this->__construct(); }
  function __construct(){
    global $ROOM, $USERS, $SELF;

    $this->actor = $USERS->ByVirtual($SELF->user_no); //仮想ユーザを取得
    if(is_null($this->actor->live) || ! $ROOM->IsOpenCast()){ //観戦モード判定
      foreach(array('blinder', 'earplug') as $role){ //本人視点が変化するタイプ
	if(($ROOM->IsEvent($role)  && $ROOM->IsDay()) ||
	   ($ROOM->IsOption($role) && ! $ROOM->IsFinished())){
	  $this->actor->virtual_live = true;
	  $this->actor->role_list[] = $role;
	}
      }
      $role = 'deep_sleep'; //爆睡者は処理の位置が違うので個別対応
      if($ROOM->IsOption($role) && ! $ROOM->IsFinished()){
	$SELF->virtual_live = true;
	$SELF->role_list[] = $role;
      }
    }
    $this->LoadExtension();
    $this->SetFlag();
  }

  //フィルタ対象役職の情報をロード
  function LoadExtension(){
    global $ROLES;

    $ROLES->actor = $this->actor;
    $this->extension_list = $ROLES->Load('talk');
  }

  //フィルタ用フラグをセット
  function SetFlag(){
    global $ROOM, $SELF;

    //フラグをセット
    $this->flag->dummy_boy = $SELF->IsDummyBoy();
    $this->flag->common    = $this->actor->IsCommon(true);
    $this->flag->wolf      = $SELF->IsWolf(true) || $this->actor->IsRole('whisper_mad');
    $this->flag->fox       = $SELF->IsFox(true);
    $this->flag->whisper   = $this->actor->IsRole('whisper_ringing');
    $this->flag->howl      = $this->actor->IsRole('howl_ringing');
    $this->flag->lovers    = $ROOM->date > 1 && $this->actor->IsRole('sweet_ringing');
    $this->flag->mind_read = $ROOM->date > 1 && ($SELF->IsLive() || $ROOM->single_view_mode);

    //発言完全公開フラグ
    /*
      + ゲーム終了後は全て表示
      + 霊界表示オン状態の死者には全て表示
      + 霊界表示オフ状態は観戦者と同じ (投票情報は表示しない)
    */
    $this->flag->open_talk = $ROOM->IsOpenData();

    foreach(array('common', 'wolf', 'fox') as $type){ //身代わり君の上書き判定
      $this->flag->$type |= $this->flag->dummy_boy;
    }
  }

  //発言テーブルヘッダ作成
  function BeginTalk($class, $id = NULL){
    $this->cache = '<table' . (is_null($id) ? '' : ' id="' . $id . '"') .
      ' class="' . $class . '">' . "\n";
  }

  //基礎発言処理
  function RawAddTalk($symbol, $user_info, $sentence, $volume, $row_class = '',
		      $user_class = '', $say_class = ''){
    global $GAME_CONF;

    if($row_class  != '') $row_class  = ' ' . $row_class;
    if($user_class != '') $user_class = ' ' . $user_class;
    if($say_class  != '') $say_class  = ' ' . $say_class;
    LineToBR($sentence);
    if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';

    $this->cache .= <<<EOF
<tr class="user-talk{$row_class}">
<td class="user-name{$user_class}">{$symbol}{$user_info}</td>
<td class="say{$say_class} {$volume}">{$sentence}</td>
</tr>

EOF;
    return true;
  }

  //標準的な発言処理
  function AddTalk($user, $talk){
    global $GAME_CONF, $RQ_ARGS, $ROOM, $USERS;

    //表示情報を抽出
    $handle_name = $user->handle_name;
    if($RQ_ARGS->add_role){ //役職表示モード対応
      $real_user = $talk->scene == 'heaven' ? $user : $USERS->ByReal($user->user_no);
      $handle_name .= $real_user->GenerateShortRoleName();
    }

    $user_info = '<font style="color:'.$user->color.'">◆</font>'.$handle_name;
    if(($talk->type == 'self_talk' && ! $user->IsRole('dummy_common')) ||
       ($user->IsRole('mind_read', 'mind_open') && $ROOM->IsNight())){
      $user_info .= '<span>の独り言</span>';
    }
    $volume = $talk->font_type;
    $sentence = $talk->sentence;
    foreach($this->extension_list as $extension){ //フィルタリング処理
      $extension->AddTalk($user, $talk, $user_info, $volume, $sentence);
    }
    return $this->RawAddTalk('', $user_info, $sentence, $volume);
  }

  //囁き処理
  function AddWhisper($role, $talk){
    global $ROLES;

    if(($user_info = $ROLES->GetWhisperingUserInfo($role, $user_class)) === false) return false;
    $volume = $talk->font_type;
    $sentence = $ROLES->GetWhisperingSound($role, $talk, $say_class);
    foreach($this->extension_list as $extension){ //フィルタリング処理
      $extension->AddWhisper($role, $talk, $user_info, $volume, $sentence);
    }
    return $this->RawAddTalk('', $user_info, $sentence, $volume, '', $user_class, $say_class);
  }

  function AddSystemTalk($sentence, $class = 'system-user'){
    LineToBR($sentence);
    $this->cache .= <<<EOF
<tr>
<td class="{$class}" colspan="2">{$sentence}</td>
</tr>

EOF;
    return true;
  }

  function AddSystemMessage($class, $sentence, $add_class = ''){
    if($add_class != '') $add_class = ' ' . $add_class;
    $this->cache .= <<<EOF
<tr class="system-message{$add_class}">
<td class="{$class}" colspan="2">{$sentence}</td>
</tr>

EOF;
    return true;
  }

  function RefreshTalk(){
    $str = $this->cache.'</table>'."\n";
    $this->cache = '';
    return $str;
  }

  function EndTalk(){
    echo $this->RefreshTalk();
  }
}
