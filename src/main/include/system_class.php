<?php
// 画像管理クラスの基底クラスを定義します。
class ImageManager{
  function GenerateTag($name, $alt, $class='icon'){
    $alt = htmlspecialchars($alt, ENT_QUOTES);
    $class = htmlspecialchars($class, ENT_QUOTES);
    return "<img class='$class' src='{$this->$name}' alt='$alt' title='$alt'>";
  }
}

//村のオプション画像パス
class RoomImage extends ImageManager{
  var $waiting = 'img/waiting.gif'; //村リストの募集中の画像
  var $playing = 'img/playing.gif'; //村リストのゲーム中の画像

  var $wish_role     = 'img/room_option_wish_role.gif';     //役割希望制
  var $real_time     = 'img/room_option_real_time.gif';     //役割希望制
  var $dummy_boy     = 'img/room_option_dummy_boy.gif';     //身代わり君使用
  var $open_vote     = 'img/room_option_open_vote.gif';     //票数公開
  var $not_open_cast = 'img/room_option_not_open_cast.gif'; //配役非公開
  var $decide        = 'img/room_option_decide.gif';        //決定者
  var $authority     = 'img/room_option_authority.gif';     //権力者
  var $poison        = 'img/room_option_poison.gif';        //埋毒者
  var $cupid         = 'img/room_option_cupid.gif';         //キューピッド

  //村の最大人数リスト (RoomConfig -> max_user_list と連動させる)
  var $max_user_list = array(
			      8 => 'img/max8.gif',   // 8人
			     16 => 'img/max16.gif',  //16人
			     22 => 'img/max22.gif'   //22人
			     );
}

//役職の画像パス
class RoleImage extends ImageManager{
  var $human              = 'img/role_human.jpg';              //村人の説明
  var $wolf               = 'img/role_wolf.jpg';               //人狼の説明
  var $wolf_partner       = 'img/role_wolf_partner.jpg';       //人狼の仲間表示
  var $mage               = 'img/role_mage.jpg';               //占い師の説明
  var $mage_result        = 'img/role_mage_result.jpg';        //占い師の結果
  var $necromancer        = 'img/role_necromancer.jpg';        //霊能者の説明
  var $necromancer_result = 'img/role_necromancer_result.jpg'; //霊能者の結果
  var $mad                = 'img/role_mad.jpg';                //狂人の説明
  var $guard              = 'img/role_guard.jpg';              //狩人の説明
  var $guard_success      = 'img/role_guard_success.jpg';      //狩人の護衛成功
  var $common             = 'img/role_common.jpg';             //共有者の説明
  var $common_partner     = 'img/role_common_partner.jpg';     //共有者の仲間表示
  var $fox                = 'img/role_fox.jpg';                //妖狐の説明
  var $fox_partner        = 'img/role_fox_partner.jpg';        //妖狐の仲間表示
  var $fox_target         = 'img/role_fox_targeted.jpg';       //妖狐が狙われた画像
  var $poison             = 'img/role_poison.jpg';             //埋毒者の説明
  var $cupid              = 'img/role_cupid.jpg';              //キューピッドの説明
  var $cupid_pair         = 'img/role_cupid_pair.jpg';         //キューピッドが結びつけた恋人表示
  var $lovers_header      = 'img/role_lovers_header.jpg';      //恋人の説明(前)
  var $lovers_footer      = 'img/role_lovers_footer.jpg';      //恋人の説明(後)
  var $authority          = 'img/role_authority.jpg';          //権力者の説明
  var $result_human       = 'img/role_result_human.jpg';       //占い師、霊能者の結果(村人)
  var $result_wolf        = 'img/role_result_wolf.jpg';        //占い師、霊能者の結果(人狼)
}

//勝利陣営の画像パス
class VictoryImage extends ImageManager{
  var $human  = 'img/victory_role_human.jpg';  //村人
  var $wolf   = 'img/victory_role_wolf.jpg';   //人狼
  var $fox    = 'img/victory_role_fox.jpg';    //妖狐
  var $lovers = 'img/victory_role_lovers.jpg'; //恋人
  var $draw   = 'img/victory_role_draw.jpg';   //引き分け
}

//音源パス
class Sound{
  var $morning          = 'swf/sound_morning.swf';          //夜明け
  var $revote           = 'swf/sound_revote.swf';           //再投票
  var $objection_male   = 'swf/sound_objection_male.swf';   //異議あり(男)
  var $objection_female = 'swf/sound_objection_female.swf'; //異議あり(女)
}
?>
