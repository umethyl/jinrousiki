<?php
// �����ƥ��å�������Ǽ���饹
class Message{
  //-- room_manger.php --//
  //CreateRoom() : ¼����
  //�����귯�Υ�����
  var $dummy_boy_comment = '�ͤϤ��������ʤ���';

  //�����귯�ΰ��
  var $dummy_boy_last_words = '�ͤϤ��������ʤ��äƸ��ä��Τˡġ�';

  //-- user_manager.php --//
  //EntryUser() : �桼����Ͽ
  //��¼��å�����
  var $entry_user = '����¼�ν����ˤ�äƤ��ޤ���';

  //-- game_view.php & OutputGameHTMLHeader() --//
  var $vote_announce = '���֤�����ޤ�����ɼ���Ƥ�������'; //���ä����»����ڤ�

  //-- game_functions.php --//
  //OutputVictory() : ¼���ܿͤξ��Է��
  //¼�;���
  var $victory_human = '[¼�;���] ¼�ͤ����Ͽ�ϵ�η���䤹�뤳�Ȥ��������ޤ���';

  //��ϵ�����;���
  var $victory_wolf = '[��ϵ�����;���] �Ǹ�ΰ�ͤ򿩤������ȿ�ϵã�ϼ��γ�ʪ�����¼���ˤ���';

  //�ŸѾ��� (¼�;�����)
  var $victory_fox1 = '[�ŸѾ���] ��ϵ�����ʤ��ʤä��������Ũ�ʤɤ⤦���ʤ�';

  //�ŸѾ��� (��ϵ������)
  var $victory_fox2 = '[�ŸѾ���] �ޥ̥��ʿ�ϵ�ɤ���٤����Ȥʤ��ưפ����Ȥ�';

  //���͡����塼�ԥåɾ���
  var $victory_lovers = '[���͡����塼�ԥåɾ���] �������ˤϲ��Ԥ�̵�Ϥ��ä��ΤǤ���';

  //����ʬ��
  var $victory_draw = '[����ʬ��] ����ʬ���Ȥʤ�ޤ���';

  //����
  var $victory_vanish = '[����ʬ��] ������ï���ʤ��ʤä��ġ�';

  //��¼
  var $victory_none = '���¤��ʹԤ��ƿͤ����ʤ��ʤ�ޤ���';

  var $win  = '���ʤ��Ͼ������ޤ���'; //�ܿ;���
  var $lose = '���ʤ������̤��ޤ���'; //�ܿ�����
  var $draw = '����ʬ���Ȥʤ�ޤ���'; //����ʬ��

  //OutputReVoteList() : ����ɼ���ʥ���
  var $revote = '����ɼ�Ȥʤ�ޤ���'; //��ɼ���
  var $draw_announce = '����ɼ�Ȥʤ�Ȱ���ʬ���ˤʤ�ޤ�'; //����ʬ������

  //OutputTalkLog() : ���á������ƥ��å���������
  var $objection = '���ְ۵ġפ򿽤�Ω�Ƥޤ���'; //�۵Ĥ���
  //var $game_start = '�ϥ����೫�Ϥ���ɼ���ޤ���' //�����೫����ɼ //���ߤ��Ի���
  var $kick_do  = '�� KICK ��ɼ���ޤ���'; //KICK ��ɼ
  var $vote_do  = '�˽跺��ɼ���ޤ���';   //�跺��ɼ
  var $wolf_eat = '��������Ĥ��ޤ���';   //��ϵ����ɼ
  var $mage_do  = '���ꤤ�ޤ�';           //�ꤤ�դ���ɼ
  var $guard_do = '�θ�Ҥ��դ��ޤ���';   //��ͤ���ɼ
  var $cupid_do = '�˰�����������ޤ���'; //���塼�ԥåɤ���ɼ

  var $morning_header = 'ī��������'; //ī�Υإå���
  var $morning_footer = '���ܤ�ī����äƤ��ޤ���'; //ī�Υեå���
  var $night = '����������Ť��Ť����뤬��äƤ��ޤ���'; //��
  var $dummy_boy = '�����귯��'; //����GM�⡼���ѥإå���

  var $wolf_howl = '���������󡦡���'; //ϵ�α��ʤ�
  var $common_talk = '�ҥ��ҥ�������'; //��ͭ�Ԥξ���

  //OutputLastWords() : �����ɽ��
  var $lastwords = '�뤬���������������˴���ʤä����ΰ���񤬸��Ĥ���ޤ���';

  //OutoutDeadManType() : �����ɽ��
  var $deadman         = '��̵�ĤʻѤ�ȯ������ޤ���'; //������ɽ��������å�����
  var $wolf_killed     = '��ϵ�α¿��ˤʤä��褦�Ǥ�'; //ϵ�ν���
  var $fox_dead        = '(�Ÿ�) ���ꤤ�դ˼��������줿�褦�Ǥ�'; //�Ѽ���
  var $poison_dead     = '���Ǥ��������˴�����褦�Ǥ�'; //���ǼԤ�ƻϢ��
  var $vote_killed     = '����ɼ�η�̽跺����ޤ���'; //�ߤ�
  var $lovers_followed = '�����ͤθ���ɤ��������ޤ���'; //���ͤθ��ɤ�����

  //OutputAbility() : ǽ�Ϥ�ɽ��
  var $ability_dead     = '���ʥ���©�䤨�ޤ���������';     //���Ǥ�����
  var $ability_wolf_eat = '���������ͤ����򤷤Ƥ�������';   //ϵ����ɼ
  var $ability_mage_do  = '�ꤦ�ͤ����򤷤Ƥ�������';       //�ꤤ�դ���ɼ
  var $ability_guard_do = '��Ҥ���ͤ����򤷤Ƥ�������';   //��ͤ���ɼ
  var $ability_cupid_do = '��ӤĤ�����ͤ�����Ǥ�������'; //���塼�ԥåɤ���ɼ

  //-- game_play.php --//
  //CheckSilence()
  var $silence = '�ۤɤ����ۤ�³����'; //���ۤǻ��ַв� (���äǻ��ַв���)
  //������ηٹ��å�����
  var $sudden_death_announce = '��ɼ��λ����ʤ����ϻष���Ϲ����Ĥ��Ƥ��ޤ��ޤ�';
  var $sudden_death = '�����������˴���ʤ�ˤʤ��ޤ���'; //������

  //��ɼ�ꥻ�å�
  var $vote_reset = '����ɼ���ꥻ�åȤ���ޤ�����������ɼ���Ƥ���������';

  //-- game_vote.php --//
  //Kick ��¼�����ä���
  var $kick_out = '������ʤ򤢤��錄����¼������ޤ���';

  //OutputVoteBeforeGame()
  var $submit_kick_do    = '�оݤ򥭥å�����˰�ɼ'; //Kick ��ɼ�ܥ���
  var $submit_game_start = '������򳫻Ϥ���˰�ɼ'; //�����೫�ϥܥ���

  //OutputVoteDay()
  var $submit_vote_do = '�оݤ�跺����˰�ɼ'; //�跺��ɼ�ܥ���

  //OutputVoteNight()
  var $submit_wolf_eat = '�оݤ�������� (����)'; //ϵ�ν���ܥ���
  var $submit_mage_do  = '�оݤ��ꤦ'; //�ꤤ�դ���ɼ�ܥ���
  var $submit_guard_do = '�оݤ��Ҥ���'; //��ͤ���ɼ�ܥ���
  var $submit_cupid_do = '�оݤ˰����������'; //���塼�ԥåɤ���ɼ�ܥ���
}
?>
