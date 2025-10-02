<?php require_once(dirname(__FILE__) . '/include/time_calc.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" href="css/rule.css">
<title>汝は人狼なりや？ <?php echo $server_comment; ?> ルール</title>
</head>
<body>
<a href="index.php">←戻る</a><br>
<img src="img/rule_title.jpg">

<hr>
<h1>＜ゲームに参加する上で重要なこと＞</h1>
<span class="worning">「ゲームのプレイ内容についてこのゲーム以外の場所で話すのはやめてください。」<br></span>
<span class="worning2">これをやってしまうとゲームがおもしろくなくなります。<br></span>
<span class="worning2">特に自分が死んだからといって役割を公開するようなことは絶対にしないでください。 <br></span>
<hr>

<h1>＜「汝は人狼なりや？」の基本ルールについて＞</h1>
<span class="caption">[ゲームの目的]</span>
<div class="info">
　ある村で人狼が村の住人に紛れ込み、夜な夜な村人を襲っているという。<br>
　困った村人たちは集会所に集まって話し合いをし、一日に一人、人狼だと思われる人を　処刑することにした。<br>
　村人は村を守るために、人狼は村を狼のものにするために、どこからかやってきた妖狐はその様子をそばで伺っています。<br>
　そしてキューピッドによって結ばれた恋人たちは、イチャつくことばかり考えています。<br>
　各役割に割り当てられたプレイヤー達は知恵を絞って自分の有利になるように処刑の話を進めていきます。<br>
</div>

<span class="caption">[勝利条件]</span><br>
<ul>
  <li>村人の勝利　人狼、妖狐、恋人を全滅させること</li>
  <li><span class="wolf">人狼</span>の勝利　妖狐、恋人を殺し、村人の人数を人狼の人数以下にすること</li>
  <li><span class="fox">妖狐</span>の勝利　恋人を全滅させ、人狼が全滅したときまたは村人の人数が人狼の人数以下になった時に生きていること</li>
  <li><span class="lovers">恋人</span>の勝利　人狼が全滅したときまたは村人の人数が人狼の人数以下になった時に、恋人が生きていること</li>
</ul>
<div class="info">
必ずしもあなたが生き残ることがこのゲームの目的ではありません。<br>
自分を犠牲にしてもあなたのチームが勝てば勝利となります。<br>
</div>

<span class="caption">[村人の人口推移]</span><br>
<div class="info">
村には特殊な能力を持った人や、考えの異なる人が居ます。<br>
下の表がその人口の推移です。
</div>
<ul>
    <li>村人側の勝利を望む人たち　村人、占い師、霊能者、狩人、共有者</li>
    <li><span class="wolf">人狼</span>側の勝利を望む人たち　人狼、狂人</li>
    <li><span class="fox">妖狐</span>側の勝利を望む人たち　妖狐</li>
    <li><span class="lovers">恋人</span>側の勝利を望む人たち　キューピッド、恋人<br>
    （キューピッドはオプション指定時のみ登場します。また、恋人は兼任役職となります）</li>
</ul>

<table class="member"><tr>
<th>全人数</th><th>村人</th><th class="wolf">人狼</th><th class="mage">占い師</th><th class="necromancer">霊能者</th><th class="wolf">狂人</th><th class="guard">狩人</th><th class="common">共有者</th><th class="fox">妖狐</th><th class="poison">埋毒者</th><th class="lovers">キューピッド</th>
</tr>
<?php
$role_list = array('human', 'wolf', 'mage', 'necromancer', 'mad', 'guard', 'common', 'fox', 'poison', 'cupid');
foreach($GAME_CONF->role_list as $key => $value){
  $tag = "<td><strong>$key</strong></td>";
  foreach($role_list as $role) $tag .= '<td>' . (int)$value[$role] . '</td>';
  echo '<tr>' . $tag . '</tr>'."\n";
}
?>
</tr></table>

<span class="caption">[役割紹介]</span><br>
<div class="info">　登場する役割について詳しく説明します。</div>

<table class="role">
<tr>
	<th>役割</th><th>--　　　説明　　　--</th>
</tr>

<tr>
	<td>村人</td>
	<td>
	<div class="info">特殊な能力はありません。しかし、アナタの知恵と勇気で村を救うことができるはずです。</div>
	</td>
</tr>

<tr>
	<td class="wolf">人狼</td>
	<td>
	<div class="info">夜の間に他の人狼と協力し村人ひとり殺害できます。<br>
	また、自分以外の人狼がだれなのか知ることができます。<br>
	あなたはその強力な力で村人を食い殺すのです。<br>
	人狼だけにしかわからない遠吠えで遠く離れていても会話することができます。<br>
	人狼同士で殺害し合うことはできません<br></div>
	</td>
</tr>

<tr>
	<td class="mage">占い師</td>
	<td>
	<div class="info">夜の間に村人ひとりを「人」か「狼」か占い、翌朝にその結果を知ることができます。<br>
	ただし解るのは「人」か「狼」かだけです。<br>
	狂人や霊能者を占っても「人」としか表示されません。<br>
	また、妖狐を占うと妖狐は死んでしまいます。占い師は妖狐の天敵です。<br>
	妖狐を占った場合の占い結果は「人」と出ます。<br>
	あなたが村人の勝利を握っています！<br></div>
	</td>
</tr>

<tr>
	<td class="necromancer">霊能者</td>
	<td>
	<div class="info">２日目以降にその日の処刑者が「人」か「狼」かを知ることができます。<br>
	霊能には一晩の時間が必要で、その結果がわかるのは翌日の朝になります。<br>
	地味ですがあなたの努力次第で大きく貢献することも不可能ではありません。<br></div>
	</td>
</tr>

<tr>
	<td class="wolf">狂人</td>
	<td>
	<div class="info">人狼を崇拝している人間で、人狼の勝利がアナタの勝利となります。<br>
	しかしあなたは村人の一人と数えられます。<br>
	あなたは占い師の振りをしたり、巧みな話術で村人を混乱におとしいれるのです。<br>
	人狼チームですが、占われても「村人」と表示されます。 <br></div>
	</td>
</tr>

<tr>
	<td class="guard">狩人</td>
	<td>
	<div class="info">２日目以降に夜の間に村人ひとりを指定し人狼の殺害から護ることができます。<br>
	しかし直接人狼に狙われると殺されてしまいます。<br>
	人狼のココロを読むのです。<br></div>
	</td>
</tr>

<tr>
	<td class="common">共有者</td>
	<td>
	<div class="info">あなたは不思議な能力でもうひとりの共有者がだれであるかを知ることができます。<br>
	また離れていても共有者同士で意思の疎通をすることができます。<br>
	共有者ということを公表することで皆の信用を得やすく、生存期間が他に比べ永い能力です。<br>
	あなたには推理する時間が与えられたのです。悩みなさい。 <br></div>
	</td>
</tr>

<tr>
	<td class="fox">妖狐</td>
	<td>
	<div class="info">あなたは人狼に狙われても殺されることはありません。<br>
	ただし占われてしまうと死んでしまいます。<br>
	村人を騙し、人狼を騙し、村を妖狐のものにするのです。<br></div>
	</td>
</tr>

<tr>
	<td class="poison">埋毒者</td>
	<td>
	<div class="info">あなたは体に少量の毒が流れています。<br>
	自身は抗体がありなんともありませんが、その血液に触れた人は死に至ると言われています。<br>
	あなたが処刑されると村の中からランダムに一人道連れにします。<br>
	人狼から襲われた場合は人狼からランダムに一人道連れにします。<br>
	あなたが恋人で、最愛の人に先立たれた場合は後を追って自殺し、道連れは出ません。<br>
	勝利条件は村人勝利です<br>
	埋毒者は村作成の時にオプションとして設定され、村の人数20人以上で登場します。<br>
	（埋毒者が登場する場合、村人の代わりとして入ります。その際村人がもう一人減り、人狼が一人増えます）<br></div>
	</td>
</tr>

<tr>
	<td class="lovers">キューピッド</td>
	<td>
	<div class="info">あなたは愛の天使です。１日目の夜に誰か２名に愛の矢を放つことでその２人を恋人にできます。<br>
	恋に落ちた二人は能力に変化はありませんが、何をおいてもお互いの生存を最優先に考えるようになります。<br>
	あなた自身の生死は勝利条件に関係しません。時には自らを犠牲にしてでも恋人たちを生き残らせるのです。<br>
	占い、霊能判定では村人判定となります。また、勝利条件判定の際にも村人とカウントされます。<br>
	キューピッドは村作成の時にオプションとして設定され、村の人数6名以上で登場します。<br></div>
	</td>
</tr>

<tr>
	<td>決定者</td>
	<td>
	<div class="info">あなたの持っている雰囲気は他人を信用させる匂いを持っています。<br>
	処刑投票の票数が同数で分かれた場合、あなたの投票したほうが優先されます。<br>
	しかしあなた自身は自分が決定者であることはわかりません。<br>
	兼任となり、他の役割のオプションとして付きます。<br>
	決定者は村作成の時にオプションとして設定され、村の人数16人以上で登場します。<br></div>
	</td>
</tr>

<tr>
	<td>権力者</td>
	<td>
	<div class="info">あなたは身なり、財産から村では一目置かれた存在になっています。<br>
	他の村からもあなたのうわさが耳に入るほどです。<br>
	その権力を振りかざし、処刑投票であなたの投票は2票分の効果を発揮します。<br>
	兼任となり、他の役割のオプションとして付きます。<br>
	権力者は村作成の時にオプションとして設定され、村の人数16人以上で登場します。<br></div>
	</td>
</tr>

<tr>
	<td class="lovers">恋人</td>
	<td>
	<div class="info">あなたはキューピッドが放った愛の矢によって恋に落ちています。<br>
	その恋は時に種族の壁を越えることもあります。<br>
	もしどちらか一方が死亡した場合、残された方も恋人の後を追って自殺してしまいます。<br>
	あなたたちは何としても生き延び、二人の愛の世界を築き上げるのです。<br>
	兼任となり、他の役割のオプションとして付きます。<br>
	恋人は村作成の時にオプションとしてキューピッドが登場するように設定され、村の人数6名以上で登場します。<br></div>
	</td>
</tr>
</table>

<hr>
<h1>＜WEB用スクリプトのシステム＞</h1>

<span class="caption">[昼の行動]<br></span>
<div class="info">
昼は集会場で皆と議論することになります。<br>
他の人の意見や能力者の発言を聞きながら自分の意見を発言していってください。<br>
また処刑する人を決めるための投票もしなければなりません。<br>
上のフレームの[投票/占う/護衛]をクリックし、処刑すべきだと思う人に投票してください。<br>
</div>

<span class="caption">[夜の行動]<br></span>
<div class="info">
夜はそれぞれ自分の家に帰り、一人で過ごすことになります。<br>
普通の人は他の人と会話できませんが狼同士は発言をすることで遠吠えとなり、会話することができます。<br>
共有者も発言することで共有者同士で会話することができます。<br>
それ以外の人達は独り言となり、死んだ天国の人からのみ その内容を知ることができます。<br>
（村立て時に「霊界で配役を公開しない」オプションが指定されている場合は狼同士か共有者同士を除いて表示されません）<br>
</div>

<span class="caption">[昼の集会所や夜の時間経過について]<br></span>
<div class="info">
一日は昼と夜に分かれており、昼は集会所で12時間の間議論、夜は集会所から離れ、個人別々に6時間の時間があります。<br>
この12時間や6時間という時間はゲーム内の仮想時間で、この時間の間で様々な行動を行います。<br>
　昼の行動： 皆と議論する、処刑の投票をする<br>
　夜の行動： 狼は作戦を立て村人一人を喰い殺す、占い師は誰か一人を占う、狩人は誰か一人の護衛に付く、<br>
　　　　　　　　１日目のみキューピッドは誰か二名に愛の矢を放ちます。<br>
</div>


<span class="caption">[消費される時間について]<br></span>
<span class="caption2">・リアルタイム制の場合<br></span><div class="info">
　　村作成のオプションで「リアルタイム制」をチェック入れていると、実時間で経過していきます。<br>
　　時間は部屋を作成した人が設定でき、トップページのゲーム一覧の「リアルタイム制」画像のAltテキストに表示されます。<br>

　　(ゲーム一覧のオプションの部分にあるリアルタイム制の画像
<?php
  echo '(<img src="' . $ROOM_IMG->real_time . '" alt="リアルタイム制　昼：' .
     $TIME_CONF->default_day . '分　夜： ' . $TIME_CONF->default_night .
     '分" title="リアルタイム制　昼：' . $TIME_CONF->default_day . '分　夜： ' .
     $TIME_CONF->default_night . '分">)';
?>
にマウスポインタを乗せると表示されます)<br>
　　ゲーム中は仮想時間と実時間の両方が表示され、仮想時間は12時間、もしくは6時間から徐々に減っていき、<br>
　　実時間が0になると同時に仮想時間も0になるように計算されています。<br>
　　リアルタイム制の村に参加する場合、ご自分のPCの時計を合わせておかないと正しい残り時間が表示されません。
</div>

<span class="caption2">・リアルタイム制でない場合<br></span><div class="info">
　　村作成のオプションで「リアルタイム制」にチェックを入れない場合はこちらになります。<br>
　　非リアルタイム制では発言することで時間が消費されます。<br>
  　　半角100文字(全角50文字)の発言で、仮想時間が昼: <?php echo $spend_day; ?> 夜: <?php echo $spend_night; ?> ずつ消費されていきます。<br>
　　（夜は人狼の発言だけ仮想時間に加算されていきます）<br>
　　たくさんの文字を使って発言するとそれだけ仮想時間の消費量が多くなります。<br>
　　しかし、半角400字以上は消費時間は加算されず半角400字の消費量と同じです。<br>
  　　一定時間( 実時間 <?php echo $silence; ?> )発言が無いと皆沈黙したこととなり、昼： <?php echo$silence_day; ?> 夜： <?php echo $silence_night; ?> が消費されてしまいます。<br>
　　黙っているとどんどん時間が消費されていきます、積極的に発言しましょう。<br>
</div>


<span class="caption">[投票について]<br></span>

<span class="caption2">・昼の処刑投票</span><br><div class="info">
　　処刑するための投票は毎日、昼に行われます。<br>
　　投票は議論中いつでも可能ですが投票をやり直すことはできません、慎重に投票先を決めてください。<br>
　　また全員が投票した場合、その時点で残り時間に関係なく即処刑が実行され夜になります。<br>
　　昼の仮想時間12時間を使いきり、それでも投票してない人は <?php echo $sudden_death; ?> 以内に投票を完了しないと突然死となり<br>
　　無条件で死亡してしまいます。<br>
　　時間がなくなってきたらすみやかに投票してください。
</div>

<span class="caption2">・夜の投票（人狼が襲う・占い師が占う・狩人が護衛する・（１日目のみ）キューピッドが愛の矢を放つ）<br></span><div class="info">
　　夜になると人狼・占い師・狩人・キューピッドはそれぞれの能力を発揮するためにターゲットを指定します。<br>
　　投票ページでターゲットを指定してください。<br>
　　人狼は全員で一人だけターゲットできます。<br>
　　占い師、狩人は個人でそれぞれ指定できます。<br>
　　キューピッドは１日目のみ、結び付けたい二人を指定してください。<br>
　　ただし、村の総人数が <?php echo $GAME_CONF->cupid_self_shoot; ?> 人に満たない場合は、必ず自分と誰かを指定してください。<br>
　　夜の仮想時間6時間を使いきり、それでも投票してない人は<?php echo $sudden_death; ?>以内に投票を完了しないと突然死となり<br>
　　無条件で死亡します。<br>
</div>


<span class="caption">[制限時間が過ぎると・・]<br></span>
<div class="info">
昼12時間、夜6時間の制限時間が過ぎると発言できなくなります。<br>
村の住人達はこれまでの情報を元に投票しなくてはなりません。<br>
投票せずに<?php echo $sudden_death; ?> 過ぎてしまうと投票されて無い方は突然死となり強制的に死んでしまいます。<br>
誰かが突然死になってしまうと投票がリセットされてしまいますので注意してください。<br>
投票は時間に余裕を持って早めにしましょう。<br>
また、制限時間が来なくても全員の投票が完了していた場合はその時点で即、次の場面（昼→夜、夜→次の日の朝）になります。<br>
人数が少なくなってきた場合、発言が少なく膠着状態になった場合などでは早めに投票するようにしましょう。<br>
</div>


<span class="caption">[遺言とは]<br></span>
<div class="info">
処刑されたり、人狼に襲われたり、妖狐が占い殺されたり、恋人が後追い自殺したりしたときに初めて公開される文書です。<br>
思っていても言えなかった事や生存者に向けたメッセージなどをあらかじめ遺言に残しておくことで、<br>
死んだときの最後の一言として以後の展開に影響を与えるかもしれません。<br>
死亡者の遺言を見ることができるのは死亡した翌日の朝です。<br>
遺言の残し方は、発言の「通常どおり発言する」のドロップダウンリストの一番下にある「遺言を残す」を選択してください。<br>
死亡すると遺言を書くことはできません、生きているうちに暇な時間を使って遺言を残してください。<br>
</div>

<hr>
<h1>＜プレイ画面説明＞</h1>
<span class="caption">[ゲーム前・生存中・ゲーム後]<br></span>
<div class="info">
フレームに分割されている上の部分は発言するための領域と投票するために使用します。<br>
下のフレームはゲームの内容を表示しています。<br>
右上にある[自動更新]は下のフレームを指定した秒数で自動更新します。<br>
[音でお知らせ]は夜が明けたときと再投票になったとき、異議ありを行ったときに音を出して通知します。<br>
[↓リスト]は村人リストを発言ログの下に表示するようにします。<br>
逆に[↑リスト]は村人リストをデフォルトの発言ログの上に表示するようにします。<br>
右上の「異議あり」ボタンを押すと特殊なメッセージと音で皆の注意を引きます。<br>
「異議あり」ボタンのカッコの中の数字は残り回数でゲーム開始前から通算で<?php echo $GAME_CONF->objection; ?>回しか使用できません。<br>
<br>
　ゲームが開始されると下のフレームで上から<br>
　　　「村の名前」<br>
　　　「残り時間」<br>
　　　「村人リスト」<br>
　　　「自分の役割(と能力の結果)」<br>
　　　「発言ログ」<br>
　　　「死亡者の表示」<br>
　　　「処刑投票の開票リスト」の順で表示されます。<br>
<br>
</div>

<h2>--実際に表示される「自分の役割（と能力の結果）」--</h2><br>

<table class="role">
<tr>
<th>役割</th><th>--　　　　自分の役割(と能力の結果)　　　　--</th>
</tr>

<tr>
<td>村人</td><td><img src="<?php echo $ROLE_IMG->human; ?>"></td>
</tr>

<tr>
<td class="wolf">人狼</td><td><img src="<?php echo $ROLE_IMG->wolf; ?>"><br>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->wolf_partner; ?>"></td><td>人狼一号</td></tr></table>
</td>
</tr>

<tr>
<td class="mage">占い師</td><td><img src="<?php echo $ROLE_IMG->mage; ?>"><br>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->mage_result; ?>"></td><td>村人一号</td><td><img src="<?php echo $ROLE_IMG->result_human; ?>"></td></tr></table>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->mage_result; ?>"></td><td>人狼一号</td><td><img src="<?php echo $ROLE_IMG->result_wolf; ?>"></td></tr></table>
</td>
</tr>

<tr>
<td class="necromancer">霊能者</td><td><img src="<?php echo $ROLE_IMG->necromancer; ?>"><br>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->necromancer_result; ?>"></td><td>村人一号</td><td><img src="<?php echo $ROLE_IMG->result_human; ?>"></td></tr></table>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->necromancer_result; ?>"></td><td>人狼一号</td><td><img src="<?php echo $ROLE_IMG->result_wolf; ?>"></td></tr></table>
</td>
</tr>

<tr>
<td class="wolf">狂人</td><td><img src="<?php echo $ROLE_IMG->mad; ?>"></td>
</tr>

<tr>
<td class="guard">狩人</td><td><img src="<?php echo $ROLE_IMG->guard; ?>"><br>
<table class="view"><tr><td>占い師一号</td><td><img src="<?php echo $ROLE_IMG->guard_success; ?>"></td></tr></table></td>
</tr>

<tr>
<td class="common">共有者</td><td><img src="<?php echo $ROLE_IMG->common; ?>"><br>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->common_partner; ?>"></td><td>共有者一号</td></tr></table></td>
</tr>

<tr>
<td class="fox">妖狐</td><td><img src="<?php echo $ROLE_IMG->fox; ?>"><br>
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->fox_target; ?>"></td></tr></table></td>
</tr>

<tr>
<td class="poison">埋毒者</td><td><img src="<?php echo $ROLE_IMG->poison; ?>"></td>
</tr>

<tr>
<td class="lovers">キューピッド</td><td><img src="<?php echo $ROLE_IMG->cupid; ?>">
<table class="view"><tr><td><img src="<?php echo $ROLE_IMG->cupid_pair; ?>"></td><td>恋人一号 恋人二号</td></tr></table></td>
</tr>

<tr>
<td>決定者</td><td>--なし--</td>
</tr>

<tr>
<td>権力者</td><td><img src="<?php echo $ROLE_IMG->authority; ?>"></td>
</tr>

<tr>
<td class="lovers">恋人</td>
<td><table class="view"><tr><td><img src="<?php echo $ROLE_IMG->lovers_header; ?>"></td><td>恋人一号</td>
<td><img src="<?php echo $ROLE_IMG->lovers_footer; ?>"></td></tr></table></td>
</tr>
</table>
<br>
<br>


<span class="caption">[ゲーム中に死亡すると]<br></span>
<div class="info">
ゲーム中に死亡した場合は天国モードになります。<br>
上のフレームは霊話発言用、中央のフレームは村での出来事、下のフレームは霊話用となっています。<br>
発言すると死亡者同士でしか交わすことのできない霊話をすることができます。<br>
また下のフレームの右上に過去の発言や投票のログを見ることができるリンクがあります。<br>
<br>
通常の天国モードでは全ての役職、遠吠え、ささやき、独り言が全て見えています。<br>
村立て時に「霊界で配役を公開しない」オプションを設定している場合、天国モードでもこれらの情報は見えません。<br>
ただし、狼と共有者は、生存している仲間の遠吠えやささやきを見ることが可能です。<br>
もちろん見るだけで、狼や共有者の霊話は生存している仲間には聞こえません。<br>
</div>

<hr>
</body>
</html>
