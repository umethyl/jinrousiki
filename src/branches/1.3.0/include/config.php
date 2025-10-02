<?php
require_once(dirname(__FILE__) . '/message_class.php'); //�����ƥ��å�������Ǽ���饹
require_once(dirname(__FILE__) . '/system_class.php');  //�����ƥ�����Ǽ���饹

//�������ƥʥ�����
class RoomConfig{
  //�����Ǹ�β��ä�����¼�ˤʤ�ޤǤλ��� (��)
  //(���ޤ�û��������������ȶ��礹���ǽ������)
  var $die_room = 1200;

  //��λ���������Υ桼���Υ��å���� ID �ǡ����򥯥ꥢ����ޤǤλ��� (��)
  var $clear_session_id = 1200;

  //����Ϳ��Υꥹ�� (RoomImage->max_user_list ��Ϣư������)
  var $max_user_list = array(8, 16, 22);

  //¼�����ѥ���� (���ʤ�Ƚ�ꥹ���å�)
  var $room_password = '';
}

//����������
class GameConfig{
  //-- ������Ͽ --//
  //��¼���� (Ʊ��������Ʊ�� IP ��ʣ����Ͽ) (true�����Ĥ��ʤ� / false�����Ĥ���)
  var $entry_one_ip_address = true;

  //�ȥ�å��б� (true���Ѵ����� / false�� "#" ���ޤޤ�Ƥ����饨�顼���֤�)
  //var $trip = true; //�ޤ���������Ƥ��ޤ���
  var $trip = false;

  //ȯ����֡פǳ��
  var $quote_words = false;

  //-- ��ɼ --//
  var $kick = 3; //��ɼ�� KICK ������Ԥ���
  var $draw = 3; //����ɼ�����ܤǰ���ʬ���Ȥ��뤫

  //-- �� --//
  //����ơ��֥�
  /* ����θ���
    [�����໲�ÿͿ�] => array([����̾1] => [����̾1�οͿ�], [����̾2] => [����̾2�οͿ�], ...),
    �����໲�ÿͿ�������̾�οͿ��ι�פ����ʤ����ϥ����೫����ɼ���˥��顼���֤�
      human       : ¼��
      wolf        : ��ϵ
      mage        : �ꤤ��
      necromancer : ��ǽ��
      mad         : ����
      guard       : ���
      common      : ��ͭ��
      fox         : �Ÿ�
      poison      : ���Ǽ�
      cupid       : ���塼�ԥå�
  */
  var $role_list = array(
     4 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1),
     5 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1, 'poison' => 1),
     6 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1, 'poison' => 1, 'cupid' => 1),
     7 => array('human' =>  3, 'wolf' => 1, 'mage' => 1, 'guard' => 1, 'fox' => 1),
     8 => array('human' =>  5, 'wolf' => 2, 'mage' => 1),
     9 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1),
    10 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1),
    11 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1),
    12 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1),
    13 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common'=> 2),
    14 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2),
    15 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    16 => array('human' =>  6, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    17 => array('human' =>  7, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    18 => array('human' =>  8, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    19 => array('human' =>  9, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    20 => array('human' => 10, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    21 => array('human' => 11, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    22 => array('human' => 12, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1)
                         );

  //���ǼԤ��ߤä��ݤ˴������ޤ���о� (true:��ɼ�ԥ����� / false:����������)
  var $poison_only_voter = false;

  //ϵ�����ǼԤ������ݤ˴������ޤ���о� (true:��ɼ�Ը��� / false:������)
  var $poison_only_eater = true;

  var $cupid_self_shoot  = 10; //���塼�ԥåɤ�¾���Ǥ���ǽ�Ȥʤ����¼�Ϳ�

  //-- �ְ۵ġפ��� --//
  var $objection = 5; //������
  var $objection_image = 'img/objection.gif'; //�ְ۵ġפ���ܥ���β����ѥ�

  //-- ��ư���� --//
  var $auto_reload = true; //game_view.php �Ǽ�ư������ͭ���ˤ��� / ���ʤ� (��������٤����)
  var $auto_reload_list = array(30, 45, 60); //��ư�����⡼�ɤι����ֳ�(��)�Υꥹ��
}

//������λ�������
class TimeConfig{
  //���ס��������Ĥ���֥���Ǥ������ͤ�᤮�����ɼ���Ƥ��ʤ��ͤ������ष�ޤ�(��)
  var $sudden_death = 180;

  //-- �ꥢ�륿������ --//
  var $default_day   = 5; //�ǥե���Ȥ�������»���(ʬ)
  var $default_night = 3; //�ǥե���Ȥ�������»���(ʬ)

  //-- ���ä��Ѥ������ۻ����� --//
  //������»���(���12���֡�spend_time=1(Ⱦ��100ʸ������) �� 12���� �� $day �ʤߤޤ�)
  var $day = 96;

  //������»���(��� 6���֡�spend_time=1(Ⱦ��100ʸ������) ��  6���� �� $night �ʤߤޤ�)
  var $night = 24;

  //��ꥢ�륿�������Ǥ������ͤ�᤮������ۤȤʤꡢ���ꤷ�����֤��ʤߤޤ�(��)
  var $silence = 60;

  //���۷в���� (12���� �� $day(��) or 6���� �� $night (��) �� $silence_pass �ܤλ��֤��ʤߤޤ�)
  var $silence_pass = 8;
}

//������ץ쥤���Υ�������ɽ������
class IconConfig{
  var $path   = './user_icon';   //�桼����������Υѥ�
  var $width  = 45;              //ɽ��������(��)
  var $height = 45;              //ɽ��������(�⤵)
  var $dead   = 'img/grave.jpg'; //���
  var $wolf   = 'img/wolf.gif';  //ϵ
}

//����������Ͽ����
class UserIcon{
  var $name   = 20;    //��������̾�ˤĤ�����ʸ����(Ⱦ��)
  var $size   = 15360; //���åץ��ɤǤ��륢������ե�����κ�������(ñ�̡��Х���)
  var $width  = 45;    //���åץ��ɤǤ��륢������κ�����
  var $height = 45;    //���åץ��ɤǤ��륢������κ���⤵
  var $number = 1000;  //��Ͽ�Ǥ��륢������κ����
}

//����ɽ������
class OldLogConfig{
  var $one_page = 20;   //����������1�ڡ����Ǥ����Ĥ�¼��ɽ�����뤫
  var $reverse  = true; //�ǥե���Ȥ�¼�ֹ��ɽ���� (true:�դˤ��� / false:���ʤ�)
}

//�ǡ�����Ǽ���饹�����
$ROOM_CONF   = new RoomConfig();   //�������ƥʥ�����
$GAME_CONF   = new GameConfig();   //����������
$TIME_CONF   = new TimeConfig();   //������λ�������
$ICON_CONF   = new IconConfig();   //�桼�������������
$ROOM_IMG    = new RoomImage();    //¼����β����ѥ�
$ROLE_IMG    = new RoleImage();    //�򿦤β����ѥ�
$VICTORY_IMG = new VictoryImage(); //�����رĤβ����ѥ�
$SOUND       = new Sound();        //���Ǥ��Τ餻��ǽ�Ѳ����ѥ�
$MESSAGE     = new Message();      //�����ƥ��å�����
?>
