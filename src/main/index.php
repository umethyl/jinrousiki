<?php require_once('include/setting.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="css/index.css">
<title>汝は人狼なりや？<?php echo $server_comment; ?></title>
</head>
<body>
<?php if($back_page != '') echo "<a href=\"$back_page\">←戻る</a>"; ?>
<a href="index.php"><img src="img/top_title.jpg"></a>
<div class="comment"><?php echo $server_comment; ?></div>
<noscript>＜＜ JavaScriptを有効にしてください ＞＞</noscript>
<table class="main">
  <tr><td>
    <div class="menu">メニュー</div>
    <ul>
      <li><a href="script_info.php">特徴と仕様</a></li>
      <li><a href="rule.php">ゲームのルール</a></li>
      <li><a href="old_log.php">ログ閲覧</a></li>
      <li>★☆★☆★☆★</li>
      <li><a href="icon_view.php">アイコン一覧</a></li>
      <li><a href="icon_upload.php">アイコン登録</a></li>
      <li>★☆★☆★☆★</li>
      <li><a href="src/">開発版ソースダウンロード</a></li>
      <li><a href="src/diff.txt">更新履歴 (テキストファイル)</a></li>
    </ul>

    <div class="menu">交流用サイト</div>
    <ul>
      <li><a href="http://www12.atpages.jp/cirno/">チルノ鯖＠式神研</a></li>
    </ul>

    <div class="menu">外部リンク</div>
    <ul>
      <li><a href="http://sourceforge.jp/projects/jinrousiki/">人狼式 (配布元)</a></li>
    </ul>
  </td>

  <td>
    <fieldset>
      <legend>Information <a href="info/index.php">〜過去のinformationはこちら〜</a></legend>
      <div class="information"><?php include 'info/top.php'; ?></div>
    </fieldset>

    <fieldset>
      <legend>ゲーム一覧</legend>
      <div class="game-list"><?php include 'room_manager.php'; ?></div>
    </fieldset>

    <form method="POST" action="room_manager.php">
    <input type="hidden" name="command" value="CREATE_ROOM">
    <fieldset>
      <legend>村の作成</legend>
      <table>
        <tr>
          <td><label>村の名前：</label></td>
          <td><input type="text" name="room_name" size="45"> 村</td>
        </tr>

        <tr>
          <td><label>村についての説明：</label></td>
          <td><input type="text" name="room_comment" size="50"></td>
        </tr>

       <tr>
         <td><label>最大人数：</label></td>
         <td>
           <select name="max_user">
             <optgroup label="最大人数">
               <option>8</option>
               <option>16</option>
               <option selected>22</option>
             </optgroup>
           </select>
         </td>
       </tr>

       <tr>
         <td><label for="wish_role">役割希望制：</label></td>
         <td class="explain">
           <input id="wish_role" type="checkbox" name="game_option_wish_role" value="wish_role">
           (希望の役割を指定できますが、なれるかは運です)
         </td>
       </tr>

       <tr>
         <td><label for="real_time">リアルタイム制：</label></td>
         <td class="explain">
           <input id="real_time" type="checkbox" name="game_option_real_time" value="real_time" checked>
           (制限時間が実時間で消費されます　昼：
           <input type="text" name="game_option_real_time_day" value="<?php echo $TIME_CONF->default_day; ?>" size="2" maxlength="2">分 夜：
           <input type="text" name="game_option_real_time_night" value="<?php echo $TIME_CONF->default_night; ?>" size="2" maxlength="2">分)
         </td>
       </tr>

       <tr>
         <td><label for="dummy_boy">初日の夜は身代わり君：</label></td>
         <td class="explain">
           <input id="dummy_boy" type="checkbox" name="game_option_dummy_boy" value="dummy_boy" checked>
           (初日の夜、身代わり君が狼に食べられます)
         </td>
       </tr>

       <tr>
         <td><label for="open_vote">投票した票数を公表する：</label></td>
         <td class="explain">
           <input id="open_vote" type="checkbox" name="game_option_open_vote" value="open_vote" checked>
          (権力者が投票でバレます)
         </td>
       </tr>

       <tr>
         <td><label for="not_open_cast">霊界で配役を公開しない：</label></td>
         <td class="explain">
           <input id="not_open_cast" type="checkbox" name="game_option_not_open_cast" value="not_open_cast">
          (霊界でも誰がどの役職なのかが公開されません)
         </td>
       </tr>

       <tr>
         <td><label for="role_decide">16人以上で決定者登場：</label></td>
         <td class="explain">
           <input id="role_decide" type="checkbox" name="option_role_decide" value="decide" checked>
           (投票が同数の時、決定者の投票先が優先されます・兼任)
         </td>
       </tr>

       <tr>
         <td><label for="role_authority">16人以上で権力者登場：</label></td>
         <td class="explain">
           <input id="role_authority" type="checkbox" name="option_role_authority" value="authority" checked>
           (投票の票数が２票になります・兼任)
         </td>
       </tr>

       <tr>
         <td><label for="role_poison">20人以上で埋毒者登場：</label></td>
         <td class="explain">
           <input id="role_poison" type="checkbox" name="option_role_poison" value="poison" checked>
           (処刑されたり狼に食べられた場合、道連れにします・村人二人→埋毒1 狼1)
         </td>
       </tr>

       <tr>
          <td><label for="role_cupid">14人もしくは16人以上で<br>　キューピッド登場：</label></td>
          <td class="explain">
            <input id="role_cupid" type="checkbox" name="option_role_cupid" value="cupid">
            (初日夜に選んだ相手を恋人にします。恋人となった二人は勝利条件が変化します)
          </td>
       </tr>

       <tr>
	 <td class="make" colspan="2">
	   <label for="room_password">村作成パスワード</label>
	   <input type="password" id="room_password" name="room_password" size="20">　
           <input type="submit" value=" 作成 ">
	 </td>
       </tr>
       </table>
    </fieldset>
    </form>
  </td></tr>
</table>

<div class="footer">
[PHP4 + MYSQLスクリプト　<a href="http://p45.aaacafe.ne.jp/~netfilms/" target="_blank">配布ホームページ</a>]
[システム　<a href="http://sourceforge.jp/projects/mbemulator/" target="_blank">mbstringエミュレータ</a>]<br>
[写真素材　<a href="http://keppen.web.infoseek.co.jp/" target="_blank">天の欠片</a>]
[フォント素材　<a href="http://azukifont.mints.ne.jp/" target="_blank">あずきフォント</a>]<br>
<?php echo 'PHP Ver. ' . PHP_VERSION . ', ' . $script_version . ', LastUpdate: ' . $script_lastupdate; ?>
</div>
</body>
</html>
