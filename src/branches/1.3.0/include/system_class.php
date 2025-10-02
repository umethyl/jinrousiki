<?php
// �����������饹�δ��쥯�饹��������ޤ���
class ImageManager{
  function GenerateTag($name, $alt, $class='icon'){
    $alt = htmlspecialchars($alt, ENT_QUOTES);
    $class = htmlspecialchars($class, ENT_QUOTES);
    return "<img class='$class' src='{$this->$name}' alt='$alt' title='$alt'>";
  }
}

//¼�Υ��ץ��������ѥ�
class RoomImage extends ImageManager{
  var $waiting = 'img/waiting.gif'; //¼�ꥹ�Ȥ��罸��β���
  var $playing = 'img/playing.gif'; //¼�ꥹ�ȤΥ�������β���

  var $wish_role     = 'img/room_option_wish_role.gif';     //����˾��
  var $real_time     = 'img/room_option_real_time.gif';     //����˾��
  var $dummy_boy     = 'img/room_option_dummy_boy.gif';     //�����귯����
  var $open_vote     = 'img/room_option_open_vote.gif';     //ɼ������
  var $not_open_cast = 'img/room_option_not_open_cast.gif'; //���������
  var $decide        = 'img/room_option_decide.gif';        //�����
  var $authority     = 'img/room_option_authority.gif';     //���ϼ�
  var $poison        = 'img/room_option_poison.gif';        //���Ǽ�
  var $cupid         = 'img/room_option_cupid.gif';         //���塼�ԥå�

  //¼�κ���Ϳ��ꥹ�� (RoomConfig -> max_user_list ��Ϣư������)
  var $max_user_list = array(
			      8 => 'img/max8.gif',   // 8��
			     16 => 'img/max16.gif',  //16��
			     22 => 'img/max22.gif'   //22��
			     );
}

//�򿦤β����ѥ�
class RoleImage extends ImageManager{
  var $human              = 'img/role_human.jpg';              //¼�ͤ�����
  var $wolf               = 'img/role_wolf.jpg';               //��ϵ������
  var $wolf_partner       = 'img/role_wolf_partner.jpg';       //��ϵ�����ɽ��
  var $mage               = 'img/role_mage.jpg';               //�ꤤ�դ�����
  var $mage_result        = 'img/role_mage_result.jpg';        //�ꤤ�դη��
  var $necromancer        = 'img/role_necromancer.jpg';        //��ǽ�Ԥ�����
  var $necromancer_result = 'img/role_necromancer_result.jpg'; //��ǽ�Ԥη��
  var $mad                = 'img/role_mad.jpg';                //���ͤ�����
  var $guard              = 'img/role_guard.jpg';              //��ͤ�����
  var $guard_success      = 'img/role_guard_success.jpg';      //��ͤθ������
  var $common             = 'img/role_common.jpg';             //��ͭ�Ԥ�����
  var $common_partner     = 'img/role_common_partner.jpg';     //��ͭ�Ԥ����ɽ��
  var $fox                = 'img/role_fox.jpg';                //�ŸѤ�����
  var $fox_partner        = 'img/role_fox_partner.jpg';        //�ŸѤ����ɽ��
  var $fox_target         = 'img/role_fox_targeted.jpg';       //�ŸѤ�����줿����
  var $poison             = 'img/role_poison.jpg';             //���ǼԤ�����
  var $cupid              = 'img/role_cupid.jpg';              //���塼�ԥåɤ�����
  var $cupid_pair         = 'img/role_cupid_pair.jpg';         //���塼�ԥåɤ���ӤĤ�������ɽ��
  var $lovers_header      = 'img/role_lovers_header.jpg';      //���ͤ�����(��)
  var $lovers_footer      = 'img/role_lovers_footer.jpg';      //���ͤ�����(��)
  var $authority          = 'img/role_authority.jpg';          //���ϼԤ�����
  var $result_human       = 'img/role_result_human.jpg';       //�ꤤ�ա���ǽ�Ԥη��(¼��)
  var $result_wolf        = 'img/role_result_wolf.jpg';        //�ꤤ�ա���ǽ�Ԥη��(��ϵ)
}

//�����رĤβ����ѥ�
class VictoryImage extends ImageManager{
  var $human  = 'img/victory_role_human.jpg';  //¼��
  var $wolf   = 'img/victory_role_wolf.jpg';   //��ϵ
  var $fox    = 'img/victory_role_fox.jpg';    //�Ÿ�
  var $lovers = 'img/victory_role_lovers.jpg'; //����
  var $draw   = 'img/victory_role_draw.jpg';   //����ʬ��
}

//�����ѥ�
class Sound{
  var $morning          = 'swf/sound_morning.swf';          //������
  var $revote           = 'swf/sound_revote.swf';           //����ɼ
  var $objection_male   = 'swf/sound_objection_male.swf';   //�۵Ĥ���(��)
  var $objection_female = 'swf/sound_objection_female.swf'; //�۵Ĥ���(��)
}
?>
