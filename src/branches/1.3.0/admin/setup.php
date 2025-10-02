<?php
require_once(dirname(__FILE__) . '/../include/functions.php');

OutputHTMLHeader('��Ͽ�ϵ�ʤ�䡩[�������]', 'action', '../css'); //HTML�إå�

if(! ($dbHandle = ConnectDatabase(true, false))){ //DB ��³
  mysql_query("CREATE DATABASE $db_name DEFAULT CHARSET ujis");
  echo "�ǡ����١��� $db_name ��������ޤ�����<br>";
  $dbHandle = ConnectDatabase(true); //����� DB ��³
}
echo '</head><body>'."\n";

CheckTable(); //�ơ��֥����
OutputHTMLFooter(); //HTML�եå�
DisconnectDatabase($dbHandle); //DB ��³���

//-- ���饹��� --//
//�桼����������ν������
//�������󥤥᡼����PHP���ֻ����ɲä�����Ϥ�����ɬ���ɲä��Ƥ���������
class DefaultIcon{
  //�桼����������ǥ��쥯�ȥꡧsetup.php ��������Хѥ�
  //�ºݤ˱��Ѥ���ݤ� TOP ��������Хѥ� (IconConfig->path) �򻲾Ȥ����������
  var $path   = '../user_icon';  //��������̾�Υꥹ��

  var $name = array('����', '�ų�', '����', '�����', '��', '�忧', '��', '��', '��', '�����鿧');

  //��������ο� (��������Υե�����̾��ɬ��001���ο����ˤ��Ƥ�������), ��, �⤵
  var $color = array('#DDDDDD', '#999999', '#FFD700', '#FF9900', '#FF0000',
		     '#99CCFF', '#0066FF', '#00EE00', '#CC00CC', '#FF9999');
  var $width  = array(32, 32, 32, 32, 32, 32, 32, 32, 32, 32);
  var $height = array(32, 32, 32, 32, 32, 32, 32, 32, 32, 32);
}

//�����귯��������
class DummyBoyIcon{
  var $path   = '../img/dummy_boy_user_icon.jpg'; //IconConfig->path ��������Хѥ�
  var $name   = '�����귯��'; //̾��
  var $color  = '#000000'; //��
  var $width  = 45; //��
  var $height = 45; //�⤵
}

//-- �ؿ� --//
//ɬ�פʥơ��֥뤬���뤫��ǧ����
function CheckTable(){
  global $ICON_CONF, $db_name, $system_password;

  //�ơ��֥�Υꥹ�Ȥ�����˼���
  $sql   = mysql_list_tables($db_name);
  $count = mysql_num_rows($sql);
  $table = array();
  for($i=0; $i < $count; $i++) array_push($table, mysql_tablename($sql, $i));

  //�����å����ƥơ��֥뤬¸�ߤ��ʤ���к�������
  if(! in_array('room', $table)){
    mysql_query("CREATE TABLE room(room_no int primary key, room_name text, room_comment text,
		max_user int, game_option text, option_role text, status text,
		date int, day_night text,last_updated text,victory_role text)");
    echo '�ơ��֥�(room)��������ޤ���<br>'."\n";
  }
  if(! in_array('user_entry', $table)){
    mysql_query("CREATE TABLE user_entry(room_no int, user_no int, uname text, handle_name text,
		icon_no int, profile text, sex text, password text, role text, live text,
		session_id char(32) unique, last_words text, ip_address text, last_load_day_night text)");
    echo '�ơ��֥�(user_entry)��������ޤ���<br>'."\n";

    mysql_query("INSERT INTO user_entry(room_no, user_no, uname, handle_name, icon_no, profile,
		password, role, live)
		VALUES(0, 0, 'system', '�����ƥ�', 1, '������ޥ�����', '$system_password', 'GM', 'live')");
  }
  if(! in_array('talk', $table)){
    mysql_query("CREATE TABLE talk(room_no int, date int, location text, uname text, time text,
		 sentence text, font_type text, spend_time int)");
    echo '�ơ��֥�(talk)��������ޤ���<br>'."\n";
  }
  if(! in_array('vote', $table)){
    mysql_query("CREATE TABLE vote(room_no int NOT NULL, date int, uname text, target_uname text,
		 vote_number int, vote_times int, situation text)");
    echo '�ơ��֥�(vote)��������ޤ���<br>'."\n";
  }
  if(! in_array('system_message', $table)){
    mysql_query("CREATE TABLE system_message(room_no int, message text, type text, date int)");
    echo '�ơ��֥�(system_message)��������ޤ���<br>'."\n";
  }
  if(! in_array('user_icon', $table)){
    mysql_query("CREATE TABLE user_icon(icon_no int primary key, icon_name text, icon_filename text,
		icon_width int, icon_height int, color text, session_id text)");
    echo '�ơ��֥�(user_icon)��������ޤ���<br>'."\n";

    //�����귯�Υ����������Ͽ(��������No��0)
    $class = new DummyBoyIcon(); //�����귯�����������������
    mysql_query("INSERT INTO user_icon(icon_no, icon_name, icon_filename, icon_width,
		 icon_height,color)
		 VALUES(0, '{$class->name}', '{$class->path}', {$class->width},
		 {$class->height}, '{$class->color}')");

    //����Υ�������Υե�����̾�ȿ��ǡ����� DB ����Ͽ����
    $icon_no = 1;
    $class = new DefaultIcon(); //�桼����������ν����������

    //�ǥ��쥯�ȥ���Υե�������������
    if($handle = opendir($class->path)){
      while (($file = readdir($handle)) !== false){
	if($file != '.' && $file != '..' && $file != 'index.html'){
	  //����ǡ������ɤ߹���
	  $name   = $class->name[  $icon_no - 1];
	  $width  = $class->width[ $icon_no - 1];
	  $height = $class->height[$icon_no - 1];
	  $color  = $class->color[ $icon_no - 1];

	  mysql_query("INSERT INTO user_icon(icon_no, icon_name, icon_filename, icon_width,
			icon_height, color)
			VALUES($icon_no, '$name', '$file', $width, $height, '$color')");
	  $icon_no++;
	  echo "�桼����������($file $name $width �� $height $color)����Ͽ���ޤ���<br>"."\n";
	}
      }
      closedir($handle);
    }
  }

  if(! in_array('admin_manage', $table)){
    mysql_query("CREATE TABLE admin_manage(session_id text)");
    mysql_query("INSERT INTO admin_manage VALUES('')");
    echo '�ơ��֥�(admin_manage)��������ޤ���<br>'."\n";
  }
  mysql_query("GRANT ALL ON {$db_name}.* TO $db_uname");
  mysql_query('COMMIT'); //������ߥå�
  echo '��������̵����λ���ޤ�����<br>'."\n";
}
?>
