<?php require_once('include/setting.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="css/index.css">
<title>��Ͽ�ϵ�ʤ�䡩<?php echo $server_comment; ?></title>
</head>
<body>
<?php if($back_page != '') echo "<a href=\"$back_page\">�����</a>"; ?>
<a href="index.php"><img src="img/top_title.jpg"></a>
<div class="comment"><?php echo $server_comment; ?></div>
<noscript>��� JavaScript��ͭ���ˤ��Ƥ������� ���</noscript>
<table class="main">
  <tr><td>
    <div class="menu">��˥塼</div>
    <ul>
      <li><a href="script_info.php">��ħ�Ȼ���</a></li>
      <li><a href="rule.php">������Υ롼��</a></li>
      <li><a href="old_log.php">������</a></li>
      <li>��������������</li>
      <li><a href="icon_view.php">�����������</a></li>
      <li><a href="icon_upload.php">����������Ͽ</a></li>
      <li>��������������</li>
      <li><a href="src/">��ȯ�ǥ��������������</a></li>
      <li><a href="src/diff.txt">�������� (�ƥ����ȥե�����)</a></li>
    </ul>

    <div class="menu">��ή�ѥ�����</div>
    <ul>
      <li><a href="http://www12.atpages.jp/cirno/">����λ���������</a></li>
    </ul>

    <div class="menu">�������</div>
    <ul>
      <li><a href="http://sourceforge.jp/projects/jinrousiki/">��ϵ�� (���۸�)</a></li>
    </ul>
  </td>

  <td>
    <fieldset>
      <legend>Information <a href="info/index.php">������information�Ϥ������</a></legend>
      <div class="information"><?php include 'info/top.php'; ?></div>
    </fieldset>

    <fieldset>
      <legend>���������</legend>
      <div class="game-list"><?php include 'room_manager.php'; ?></div>
    </fieldset>

    <form method="POST" action="room_manager.php">
    <input type="hidden" name="command" value="CREATE_ROOM">
    <fieldset>
      <legend>¼�κ���</legend>
      <table>
        <tr>
          <td><label>¼��̾����</label></td>
          <td><input type="text" name="room_name" size="45"> ¼</td>
        </tr>

        <tr>
          <td><label>¼�ˤĤ��Ƥ�������</label></td>
          <td><input type="text" name="room_comment" size="50"></td>
        </tr>

       <tr>
         <td><label>����Ϳ���</label></td>
         <td>
           <select name="max_user">
             <optgroup label="����Ϳ�">
               <option>8</option>
               <option>16</option>
               <option selected>22</option>
             </optgroup>
           </select>
         </td>
       </tr>

       <tr>
         <td><label for="wish_role">����˾����</label></td>
         <td class="explain">
           <input id="wish_role" type="checkbox" name="game_option_wish_role" value="wish_role">
           (��˾���������Ǥ��ޤ������ʤ�뤫�ϱ��Ǥ�)
         </td>
       </tr>

       <tr>
         <td><label for="real_time">�ꥢ�륿��������</label></td>
         <td class="explain">
           <input id="real_time" type="checkbox" name="game_option_real_time" value="real_time" checked>
           (���»��֤��»��֤Ǿ��񤵤�ޤ����롧
           <input type="text" name="game_option_real_time_day" value="<?php echo $TIME_CONF->default_day; ?>" size="2" maxlength="2">ʬ �롧
           <input type="text" name="game_option_real_time_night" value="<?php echo $TIME_CONF->default_night; ?>" size="2" maxlength="2">ʬ)
         </td>
       </tr>

       <tr>
         <td><label for="dummy_boy">��������Ͽ����귯��</label></td>
         <td class="explain">
           <input id="dummy_boy" type="checkbox" name="game_option_dummy_boy" value="dummy_boy" checked>
           (�������롢�����귯��ϵ�˿��٤��ޤ�)
         </td>
       </tr>

       <tr>
         <td><label for="open_vote">��ɼ����ɼ�����ɽ���롧</label></td>
         <td class="explain">
           <input id="open_vote" type="checkbox" name="game_option_open_vote" value="open_vote" checked>
          (���ϼԤ���ɼ�ǥХ�ޤ�)
         </td>
       </tr>

       <tr>
         <td><label for="not_open_cast">��������������ʤ���</label></td>
         <td class="explain">
           <input id="not_open_cast" type="checkbox" name="game_option_not_open_cast" value="not_open_cast">
          (��Ǥ�ï���ɤ��򿦤ʤΤ�����������ޤ���)
         </td>
       </tr>

       <tr>
         <td><label for="role_decide">16�Ͱʾ�Ƿ�����о졧</label></td>
         <td class="explain">
           <input id="role_decide" type="checkbox" name="option_role_decide" value="decide" checked>
           (��ɼ��Ʊ���λ�������Ԥ���ɼ�褬ͥ�褵��ޤ�����Ǥ)
         </td>
       </tr>

       <tr>
         <td><label for="role_authority">16�Ͱʾ�Ǹ��ϼ��о졧</label></td>
         <td class="explain">
           <input id="role_authority" type="checkbox" name="option_role_authority" value="authority" checked>
           (��ɼ��ɼ������ɼ�ˤʤ�ޤ�����Ǥ)
         </td>
       </tr>

       <tr>
         <td><label for="role_poison">20�Ͱʾ�����Ǽ��о졧</label></td>
         <td class="explain">
           <input id="role_poison" type="checkbox" name="option_role_poison" value="poison" checked>
           (�跺���줿��ϵ�˿��٤�줿��硢ƻϢ��ˤ��ޤ���¼����͢�����1 ϵ1)
         </td>
       </tr>

       <tr>
          <td><label for="role_cupid">14�ͤ⤷����16�Ͱʾ��<br>�����塼�ԥå��о졧</label></td>
          <td class="explain">
            <input id="role_cupid" type="checkbox" name="option_role_cupid" value="cupid">
            (�������������������ͤˤ��ޤ������ͤȤʤä���ͤϾ�����郎�Ѳ����ޤ�)
          </td>
       </tr>

       <tr>
	 <td class="make" colspan="2">
	   <label for="room_password">¼�����ѥ����</label>
	   <input type="password" id="room_password" name="room_password" size="20">��
           <input type="submit" value=" ���� ">
	 </td>
       </tr>
       </table>
    </fieldset>
    </form>
  </td></tr>
</table>

<div class="footer">
[PHP4 + MYSQL������ץȡ�<a href="http://p45.aaacafe.ne.jp/~netfilms/" target="_blank">���ۥۡ���ڡ���</a>]
[�����ƥࡡ<a href="http://sourceforge.jp/projects/mbemulator/" target="_blank">mbstring���ߥ�졼��</a>]<br>
[�̿��Ǻࡡ<a href="http://keppen.web.infoseek.co.jp/" target="_blank">ŷ�η���</a>]
[�ե�����Ǻࡡ<a href="http://azukifont.mints.ne.jp/" target="_blank">�������ե����</a>]<br>
<?php echo 'PHP Ver. ' . PHP_VERSION . ', ' . $script_version . ', LastUpdate: ' . $script_lastupdate; ?>
</div>
</body>
</html>
