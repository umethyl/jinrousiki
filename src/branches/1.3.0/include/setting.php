<?php
//�����Ф�URL
$site_root = 'http://localhost/jinro/';

//�����ФΥ�����
$server_comment = '';

//�ǡ����١��������ФΥۥ���̾ hostname:port
//�ݡ����ֹ���ά����ȥǥե���ȥݡ��Ȥ����åȤ���ޤ���(MySQL:3306)
$db_host = 'localhost';

//�ǡ����١����Υ桼��̾
$db_uname = 'xxxxxx';

//�ǡ����١��������ФΥѥ����
$db_pass = 'xxxxxx';

//�ǡ����١���̾
$db_name = 'jinrou';

//�������ѥѥ����
$system_password = 'xxxxxx';

//���������åץ��ɥե�����Υѥ����
$src_upload_password = 'upload';

//�����Υڡ���
$back_page = '';

//�ǥХå��⡼�ɤΥ���/����
$DEBUG_MODE = false;

//���� (�ÿ�)
$OFFSET_SECONDS = 32400; //9����

//�����ե�������ɤ߹���
require_once(dirname(__FILE__) . '/config.php');          //���٤�����
require_once(dirname(__FILE__) . '/version.php');         //�С���������
require_once(dirname(__FILE__) . '/contenttype_set.php'); //�إå���ʸ������������
require_once(dirname(__FILE__) . '/functions.php');       //���ܴؿ�
require_once(dirname(__FILE__) . '/../paparazzi.php');    //�ǥХå���
if(FindDangerValue($_REQUEST) || FindDangerValue($_SERVER)) die;
?>
