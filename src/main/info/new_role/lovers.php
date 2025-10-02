<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
OutputRolePageHeader('恋人陣営');
?>
<p>
<a href="#cupid_group">キューピッド系</a>
<a href="#angel_group">天使系</a>
</p>

<h2><a id="cupid_group">キューピッド系</a></h2>
<p>
<a href="#cupid">キューピッド</a>
<a href="#self_cupid">求愛者</a>
<a href="#moon_cupid">かぐや姫</a>
<a href="#mind_cupid">女神</a>
<a href="#sweet_cupid">弁財天</a>
<a href="#triangle_cupid">小悪魔</a>
</p>

<h3><a id="cupid">キューピッド</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.2.0～]</h3>
<pre>
恋人陣営の基本種。
</pre>

<h3><a id="self_cupid">求愛者</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α21～]</h3>
<pre>
自分撃ち固定のキューピッド。
矢を撃った相手に自分を対象にした<a href="sub_role.php#mind_receiver">受信者</a>が付く。
</pre>
<h5>Ver. 1.4.0 α22～</h5>
<pre>
矢を撃った相手に自分を対象にした<a href="sub_role.php#mind_receiver">受信者</a>が付く。
</pre>
<h4>同一表示役職</h4>
<pre>
<a href="chiroptera.php#dummy_chiroptera">夢求愛者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
他の国に実在する役職です。
対象が制限される代わりに、相手にメッセージを (一方的に) 送ることができます。
思う存分自分の想いを語ってください。
</pre>

<h3><a id="moon_cupid">かぐや姫</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<pre>
自分撃ち固定で、矢を撃った二人に<a href="sub_role.php#challenge_lovers">難題</a>を付加するキューピッド。
自分に矢を撃った相手を対象にした<a href="sub_role.php#mind_receiver">受信者</a>が付く。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
バーボンハウス鯖＠東方陰陽鉄の管理人さんへの誕生日プレゼントです。
かぐや姫の不老不死の秘薬の伝説を元に、序盤は無敵だけど後半は月に帰る
(死亡する) 可能性が高くなるカップルを再現してみました。
</pre>

<h3><a id="mind_cupid">女神</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 α23～]</h3>
<pre>
矢を撃った二人を<a href="sub_role.php#mind_friend">共鳴者</a>にする上位キューピッド。
他人撃ちの場合は、さらに自分が二人を対象にした<a href="sub_role.php#mind_receiver">受信者</a>になる。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
会話能力を持った恋人を作る上位キューピッドです。
自分撃ちの場合は<a href="#self_cupid">求愛者</a>の相互撃ちと同様の状態になります。
また、他人撃ちでも<a href="sub_role.php#mind_receiver">受信者</a>になるので、矢を撃った対象の発言が
必ず見えることになります。
</pre>

<h3><a id="sweet_cupid">弁財天</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β22～]</h3>
<pre>
矢を撃った二人を<a href="sub_role.php#mind_friend">共鳴者</a>にする上位キューピッド。
また、処刑者決定後に、自分が処刑されず、投票先が処刑者ではなかったら
<a href="sub_role.php#sweet_ringing">恋耳鳴</a>を付加する。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
東方陰陽鉄のプレイヤーさんとの雑談から生まれた役職です。
テーマは「恋人の甘い会話を雰囲気だけおすそ分け」で、実利はあまりないですね。
むしろ能力を発動すると不利になるので自打ちの場合は注意が必要です。
</pre>

<h3><a id="triangle_cupid">小悪魔</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<pre>
矢を三本撃てるキューピッド。
他人撃ち制限などは普通のキューピッドと同じ。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
翠星石鯖＠やる夫人狼で一時的に施されていた改造に別名を与えてみました。
</pre>

<h2><a id="angel_group">天使系</a></h2>
<p>
<a href="#angel">天使</a>
<a href="#rose_angel">薔薇天使</a>
<a href="#lily_angel">百合天使</a>
<a href="#exchange_angel">魂移使</a>
<a href="#ark_angel">大天使</a>
<a href="#sacrifice_angel">守護天使</a>
</p>

<h3><a id="angel">天使</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<pre>
矢を撃った二人が男女だった場合に<a href="sub_role.php#mind_sympathy">共感者</a>を付加する、天使系の基本種。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
組み合わせ次第で特典が追加されるキューピッドの上位種です。
鉄板カップルが増えることを狙って作成してみました。
</pre>

<h3><a id="rose_angel">薔薇天使</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<pre>
矢を撃った二人が男性同士だった場合に<a href="sub_role.php#mind_sympathy">共感者</a>を付加する天使。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#angel">天使</a>の男性版です。
<a href="#angel">天使</a>とは逆に予想外のカップリングが増えるかもしれませんね。
</pre>

<h3><a id="lily_angel">百合天使</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<pre>
矢を撃った二人が女性だった場合に<a href="sub_role.php#mind_sympathy">共感者</a>を付加する天使。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#angel">天使</a>の女性版です。
<a href="human.php#sex_mage">ひよこ鑑定士</a>対策で性別を偽るケースがあることに気をつけましょう。
</pre>

<h3><a id="exchange_angel">魂移使</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β11～]</h3>
<pre>
矢を撃った二人を<a href="sub_role.php#possessed_exchange">交換憑依</a>させてしまう特殊な天使。
</pre>
<ol>
  <li>矢が競合した場合は抽選が発生し、一組だけが入れ替わります。<br>
    例) A-B・B-C と矢を撃たれた → A-B または B-C のどちらかだけが入れ替わる
  </li>
  <li>憑依能力者 (<a href="wolf.php#possessed_wolf">憑狼</a>・<a href="wolf.php#possessed_mad">犬神</a>・<a href="fox.php#possessed_fox">憑狐</a>) が対象だった場合は交換憑依は発生しません。</li>
  <li>他人撃ちをした場合、矢を撃った本人には交換憑依が成立したかどうかは分かりません。</li>
</ol>
<h4>[作成者からのコメント]</h4>
<pre>
ラブコメでよくある「中の人が入れ替わってしまう」展開を再現してみました。
これにぴったりくる実在する名前を思いつかなかったので造語を充てました。
「たまうつし」と読みます。
</pre>

<h3><a id="ark_angel">大天使</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β8～]</h3>
<pre>
他の<a href="#angel_group">天使系</a>が作成した<a href="sub_role.php#mind_sympathy">共感者</a>の結果を見ることができる上位天使。
ただし、本人は<a href="sub_role.php#mind_sympathy">共感者</a>を作ることはできない。
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="#angel">天使</a>の作成中の状態に別名を与えてみました。
組み合わせ次第で、二日目の朝に内訳をほぼ掌握することが可能になります。
</pre>

<h3><a id="sacrifice_angel">守護天使</a> (占い結果：村人 / 霊能結果：村人) [Ver. 1.4.0 β18～]</h3>
<h4>[耐性] 人狼襲撃：無効</h4>
<pre>
矢を撃った相手に<a href="sub_role.php#mind_sympathy">共感者</a>と<a href="sub_role.php#protected">庇護者</a>を付加する上位天使。
人狼に襲撃されても死亡しない (襲撃は失敗扱い)。
襲撃者が<a href="wolf.php#sirius_wolf">天狼</a> (完全覚醒状態) だった場合は耐性無効。
自分撃ちをしても自分に<a href="sub_role.php#protected">庇護者</a>は付かない。
</pre>
<h4>関連役職</h4>
<pre>
<a href="vampire.php#doom_vampire">冥血鬼</a>・<a href="ability.php#sacrifice">身代わり能力者</a>
</pre>
<h4>[作成者からのコメント]</h4>
<pre>
<a href="vampire.php#sacrifice_vampire">吸血公</a>の能力を恋人陣営向けに転化してみました。
<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1246414115/467" target="_top">新役職考案スレ</a> の 467 が原型です。
</pre>
</body></html>
