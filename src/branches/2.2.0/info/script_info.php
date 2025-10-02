<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('cache_config', 'message', 'user_icon_class', 'room_option_class');
Loader::LoadClass('InfoTime');
InfoHTML::OutputHeader('仕様', 0, 'script_info');
?>
<img src="../img/script_info_title.jpg" title="スクリプトの仕様" alt="スクリプトの仕様">
<ul>
  <li><a href="#environment">ゲームに参加するために必要な環境</a></li>
  <li><a href="#difference">他のスクリプトとどこが違うの？</a></li>
  <li><a href="#faq">FAQ (よくある質問と答え)</a></li>
</ul>

<h2 id="environment">ゲームに参加するために必要な環境</h2>
<div>
ゲームに参加するためには以下の条件を満たしてください。
<ul>
  <li>JavaScript を有効にする</li>
  <li>Cookie を有効にする</li>
</ul>
動作確認しているブラウザは Windows Internet Explorer 8、FireFox、Opera です。<br>
また画面の解像度は 1024x768 以上が望ましいです。<br>
</div>

<h2 id="difference">他のスクリプトとどこが違うの？</h2>
<div>
この PHP+MySQL 用のスクリプトは、「<a href="http://f45.aaa.livedoor.jp/~netfilms/">汝は人狼なりや？の PHP+MySQL 移植版(from ふたば)</a>」のソースコードを基に改良・新機能を追加したものです。<br>
以下は、基にしたスクリプトの説明です。
<blockquote>
この PHP+MySQL 用のスクリプトは人狼 CGI(perl) スクリプトの本家、<a href="http://park1.wakwak.com/~aa1/table/index.htm" target="_blank">Table@</a>さんのシステムを参考に改良をしたものです。<br>
Perl から PHP にすることで動作を高速にし、排他制御を MySQL に任せることでロックエラーの回避を目的に作成されました。<br>
本家のスクリプトとは多少の違いがあります、下記にその内容を記載します。
</blockquote>
</div>

<ul>
  <li><a href="#difference_title_system">システム関連</a>
  <li><a href="#difference_title_role">役職関連</a>
  <li><a href="#difference_title_room">村作成関連</a>
  <li><a href="#difference_title_time">時間関連</a>
  <li><a href="#difference_title_network">外部サーバ連携</a>
  <li><a href="#difference_cache">データキャッシュ</a></li>
  <li><a href="#difference_title_limit">制限事項</a>
</ul>

<h3 id="difference_title_system">システム関連</h3>
<ul>
  <li><a href="#difference_gm">ゲームマスターの必要の無いシステムです</a></li>
  <li><a href="#difference_dummy_boy">初日の夜は身代わり君</a></li>
  <li><a href="#difference_icon">ユーザの似顔絵などを表すユーザアイコンを自由にアップロードできます</a></li>
  <li><a href="#difference_vote">投票ページを別に用意</a></li>
  <li><a href="#difference_kick">キック投票</a></li>
  <li><a href="#difference_draw">自動引き分け判定</a></li>
  <li><a href="#difference_die_room">自動廃村</a></li>
  <li><a href="#difference_message">システムメッセージを画像に</a></li>
  <li><a href="#difference_deadman">死亡者の順序がランダム表示</a></li>
  <li><a href="#difference_last_words">遺言</a></li>
  <li><a href="#difference_night_talk">夜の独り言</a></li>
  <li><a href="#difference_auto_reload">自動リロード</a></li>
  <li><a href="#difference_sound">音でお知らせ</a></li>
  <li><a href="#difference_objection">異議ありボタン</a></li>
  <li><a href="#difference_icon_view">アイコン表示</a></li>
  <li><a href="#difference_name_view">ユーザ名表示</a></li>
  <li><a href="#difference_trip">トリップ</a></li>
</ul>

<h4 id="difference_gm">ゲームマスターの必要の無いシステムです</h4>
<div>
このスクリプトではゲームマスターは居ません。<br>
村を作成した人は改めて住民登録をしてゲームにご参加ください。<br>
ゲームを開始するにはプレイヤー全員が「ゲームの開始」に投票する必要があります。<br>
ゲーム中の仮想時間 (昼12時間、夜6時間) を経過した後に [ <?php echo InfoTime::$sudden_death; ?> ] 経過すると投票していない人は自動で突然死となります。<br>
突然死が発生するとその日の投票がリセットされて再投票となるので注意してください。
</div>

<h4 id="difference_dummy_boy">初日の夜は身代わり君</h4>
<div>
初日の夜に一度も発言することなく<a href="rule.php#rule_role_wolf">人狼</a>に襲われて、ゲームに参加したとは言えない！と思ったことはありませんか？<br>
村を作成するときに「<a href="game_option.php#dummy_boy">初日の夜は身代わり君</a>」にチェックを入れると初日の夜、<a href="rule.php#rule_role_wolf">人狼</a>は身代わり君しか襲えないようになります。<br>
身代わり君はプレイヤーが操作するのではなく、初日に襲われる為だけに存在します。<br>
割り当てられる役割は [ <?php Info::OutputDisableDummyBoyRole(); ?> ] 以外のどれかランダムに設定されます。
</div>

<h4 id="difference_icon">ユーザの似顔絵などを表すユーザアイコンを自由にアップロードできます</h4>
<div>
<a href="../icon_upload.php" target="_top">専用のページ</a>から [ <?php printf('%s、容量 %s', UserIcon::GetSizeLimit(), UserIcon::GetFileLimit()); ?> ] のファイルをアップロードできます。<br>
登録数の上限は [ <?php printf('%d個', UserIconConfig::NUMBER); ?> ] です。
</div>

<h4 id="difference_vote">投票ページを別に用意</h4>
<div>
今までのシステムでは、一番上に表示されているプレイヤーに誤って投票してしまうことがありました。<br>
その問題を解消するために投票のページを別に設け、ドロップダウンリストからラジオボタンに変更しました。
</div>

<h4 id="difference_kick">キック投票</h4>
<div>
村人登録後に急な用事が入って抜けなければならなくなったり、応答が無くなってしまったなどの理由で開始前に村から去ってもらうためには、KICK 投票をする必要があります。<br>
現在の設定は [ <?php printf('%d票', GameConfig::KICK); ?> ] 必要で、[ <?php printf('自己投票%s', GameConfig::SELF_KICK ? '可' : '不可'); ?> ] になっています。
</div>
<h5>Ver. 1.4.0 α21～</h5>
<div>
自己投票機能の実装
</div>

<h4 id="difference_draw">自動引き分け判定</h4>
<div>
再投票が何度も続くとゲームが進まなくなります。<br>
この場合、やむを得ず引き分けとすることが必要です。<br>
[ <?php printf('%d回', GameConfig::DRAW); ?> ] 再投票が続いた場合は自動的に引き分けとなり、ゲームは終了します。
</div>

<h4 id="difference_die_room">自動廃村</h4>
<div>
ゲームが開始されない場合、最後に発言された時間から [ <?php InfoTime::Output(RoomConfig::DIE_ROOM); ?> ] 放置されると自動で村は廃墟になります。<br>
手動で廃村にする方法はありません。連絡用の掲示板やゲーム内の発言で村に登録しないように促してください。
</div>

<h4 id="difference_message">システムメッセージを画像に</h4>
<div>
システムメッセージがテキストの場合、そのテキストをコピー＆ペーストをして発言することで本物の能力者であると信頼を得ようとすることを抑止するために画像にしてあります。<br>
どのような画像が表示されるかは<a href="rule.php#display">ルール</a>を参照してください。
</div>

<h4 id="difference_deadman">死亡者の順序がランダム表示</h4>
<div>
<a href="rule.php#rule_role_wolf">人狼</a>に襲われて死亡した場合、<a href="rule.php#rule_role_fox">妖狐</a>が占われて死亡した場合、<a href="rule.php#rule_role_poison">埋毒者</a>に道連れにされた場合、表示されるメッセージは [ ～<?php echo Message::$deadman; ?> ] となります。<br>
また、<a href="rule.php#rule_role_lovers">恋人</a>が後追いした場合、表示されるメッセージは [ ～<?php echo Message::$lovers_followed; ?> ] となります。<br>
表示される順番ですが、どの死に方をした人が上に表示されるということはなく順序がランダムに表示されます。<br>
注意しなければいけないことはリロードするたびにランダムに順序が変更されるということです。
</div>

<h4 id="difference_last_words">遺言</h4>
<div>
処刑されたり、<a href="rule.php#rule_role_wolf">人狼</a>に襲われたり、<a href="rule.php#rule_role_fox">妖狐</a>が占われて死亡した時にあらかじめ設定しておいた遺言が次の日の朝に公開されます。<br>
これは昼の会議中になんとなく言えなかったことや自分の考えをまとめたものを書いておくことで、もしもの時に効果を発揮します。<br>
遺言でさらなる情報を得て、推理の材料にしてください。<br>
設定方法は発言の文字の大きさ (強く発言する・通常どおり発言する・弱く発言する) の欄の一番下に「遺言を残す」という項目があります。<br>
この項目を選択して文章を送信すれば遺言がセットされます。<br>
「半角スペース一つ」のみを遺言にセットすることで遺言を消去できます。<br>
死亡後は遺言のセットはできません。<br>
サーバ管理者が設定することで遺言の設定をゲーム開始前に限定できます。<br>
現在の設定は [ <?php printf('遺言制限%s', GameConfig::LIMIT_LAST_WORDS ? 'あり' : 'なし'); ?> ] です。
</div>
<h5>Ver. 2.0.0 RC1～</h5>
<div>
遺言制限機能実装
</div>
<h5>Ver. 1.4.9 / Ver. 1.5.0 β1～</h5>
<div>
「半角スペース一つ」のみを遺言にセットすることで遺言を消去できます。
</div>

<h4 id="difference_night_talk">夜の独り言</h4>
<div>
<a href="rule.php#rule_role_wolf">人狼</a>、<a href="rule.php#rule_role_common">共有者</a>以外は夜に会話することは出来ませんが、発言すると独り言となり、本人と死亡者(天国モード)からは見ることができます。<br>
ただし、「<a href="game_option.php#not_open_cast"><?php OptionManager::OutputCaption('not_open_cast'); ?></a>」オプションが設定されている場合は見えません。<br>
暇つぶしにでも使ってください。
</div>

<h4 id="difference_auto_reload">自動リロード</h4>
<div>
自動でリロードするように設定することができます。<br>
リロード間隔は、[ <?php Info::OutputAutoReload(); ?> ] のどれかを設定することができます。<br>
サーバ管理者が設定することで観戦画面でも使用できます (現在の設定は [ <?php echo GameConfig::AUTO_RELOAD ? '有効' : '無効'; ?> ] です)。
</div>

<h4 id="difference_sound">音でお知らせ</h4>
<div>
「音」を ON にすると、「ゲーム開始前で人数が変動した時」「ゲーム開始前で満員になった時」<br>
「夜が明けた時」「再投票になった時」「未投票者への告知 (超過時間経過1分毎)」<br>
「未投票者への警告 (超過時間残り [ <?php InfoTime::Output(TimeConfig::ALERT); ?> ] より、[ <?php InfoTime::Output(TimeConfig::ALERT_DISTANCE); ?> ] 毎) 」「異議ありの時」に音でお知らせしてくれます。<br>
「異議あり」については<a href="#difference_objection">別項目</a>で説明します。
</div>
<h5>Ver. 1.4.14～ / Ver. 1.5.0 ～</h5>
<div>
「未投票者への告知」「未投票者への警告」でも音が鳴ります。
</div>
<h5>Ver. 1.4.4～ / Ver. 1.5.0 α4～</h5>
<div>
「ゲーム開始前で人数が変動した時」「ゲーム開始前で満員になった時」にも音が鳴ります。
</div>

<h4 id="difference_objection">異議ありボタン</h4>
<div>
ゲーム前、ゲーム中の昼に右上に「異議あり」のボタンがあります。<br>
このボタンを押すと性別に応じた特殊なメッセージと音で皆に知らせることができます。<br>
ボタンに右にカッコ内で表示されている数字は残り回数です。<br>
[ <?php printf('%d回', GameConfig::OBJECTION); ?> ] 「異議あり」を使用すると二度と使えなくなります。
</div>

<h4 id="difference_icon_view">アイコン表示 [Ver. 2.1.0 β1～]</h4>
<div>
「アイコン」を ON にすると、発言の横にアイコンが表示されるようになります。
</div>

<h4 id="difference_name_view">ユーザ名表示 [Ver. 2.1.0 β2～]</h4>
<div>
「名前」を ON にすると、発言の横にユーザ情報が表示されるようになります (ゲーム終了後限定)。
</div>

<h4 id="difference_trip">トリップ [Ver. 1.4.0 β8～]</h4>
<div>
村人登録時に、ユーザ名の入力欄にユーザ名に続けて「#任意の文字列」と入力することでトリップ変換されます。<br>
また、ユーザ名の「#」の右側のトリップ入力専用欄を使用することで「#」の入力の手間を省くことができます。<br>
現在の設定は [ <?php printf('トリップ使用%s', GameConfig::TRIP ? '可' : '不可'); ?> ] になっています。
</div>
<h5>Ver. 1.5.0 β6～</h5>
<div>
トリップ入力専用欄の実装。
</div>

<h3 id="difference_title_role">役職関連</h3>
<ul>
  <li><a href="#difference_ability_result">占い師、霊能者の結果は次の日の朝に出る</a></li>
  <li><a href="#difference_ability_cancel">同日の夜に占い師が妖狐を占い、人狼がその占い師を襲った場合は占い無効</a></li>
  <li><a href="#difference_common_talk">共有者の夜の会話が可能になりました</a></li>
  <li><a href="#difference_fox">妖狐は15人以上で常に登場</a></li>
  <li><a href="#difference_poison_vote">埋毒者を吊った際に巻き添えにする対象を限定可能</a></li>
  <li><a href="#difference_poison_eat">人狼が埋毒者を襲撃した際に巻き添えになる対象を限定可能</a></li>
</ul>

<h4 id="difference_ability_result">占い師、霊能者の結果は次の日の朝に出る</h4>
<div>
<a href="rule.php#rule_role_mage">占い師</a>は夜に占いますが、占った直後に結果がわかるのではなく次の日の朝に結果が表示されます。<br>
<a href="rule.php#rule_role_necromancer">霊能者</a>も処刑した日の夜にわかるのではなく、次の日の朝に結果が表示されます。
</div>

<h4 id="difference_ability_cancel">同日の夜に占い師が妖狐を占い、人狼がその占い師を襲った場合は占い無効</h4>
<div>
通常占い師が<a href="rule.php#rule_role_fox">妖狐</a>を占うと占われた<a href="rule.php#rule_role_fox">妖狐</a>は死んでしまいますが、同日に<a href="rule.php#rule_role_wolf">人狼</a>がその<a href="rule.php#rule_role_mage">占い師</a>を襲うと占いは失敗となり<a href="rule.php#rule_role_fox">妖狐</a>は死なずに済みます。<br>
勝率の低い<a href="rule.php#rule_role_fox">妖狐</a>のバランスを取るためにこのようになっています。
</div>

<h4 id="difference_common_talk">共有者の夜の会話が可能になりました</h4>
<div>
<a href="rule.php#rule_role_common">共有者</a>に新しい能力が増え、夜中に<a href="rule.php#rule_role_common">共有者</a>同士で会話することができます。<br>
この会話は<a href="#difference_spend_time">非リアルタイム制</a>の場合の会話による時間消費には加算されません。
</div>

<h4 id="difference_fox">妖狐は15人以上で常に登場</h4>
<div>
<a href="rule.php#rule_role_fox">妖狐</a>が登場しない村が少ないようでしたので、常に登場するようにしました。
</div>

<h4 id="difference_poison_vote">埋毒者を吊った際に巻き添えにする対象を限定可能 [Ver. 1.3.1～ / Ver. 1.4.0 α12～]</h4>
<div>
サーバ管理者がゲーム設定を変更する事で<a href="rule.php#rule_role_poison">埋毒者</a>を処刑した際に巻き添えにする対象を限定する事が可能です。<br>
現在の設定は [ <?php printf('%sからランダム', GameConfig::POISON_ONLY_VOTER ? '投票者' : '生存者全員'); ?> ] です。
</div>

<h4 id="difference_poison_eat">人狼が埋毒者を襲撃した際に巻き添えになる対象を限定可能 [Ver. 1.3.0～]</h4>
<div>
サーバ管理者がゲーム設定を変更する事で<a href="rule.php#rule_role_wolf">人狼</a>が<a href="rule.php#rule_role_poison">埋毒者</a>を襲撃した際に巻き添えになる対象を限定する事が可能です。<br>
現在の設定は [ <?php echo GameConfig::POISON_ONLY_EATER ? '襲撃者固定' : '<a href="rule.php#rule_role_wolf">人狼</a>全員からランダム'; ?> ] です。
</div>

<h3 id="difference_title_room">村作成関連</h3>
<ul>
  <li><a href="#difference_max_user">村の最大人数を制限できます</a></li>
  <li><a href="#difference_active_room">同時稼働できる村の数</a></li>
  <li><a href="#difference_establish_wait">次の村を立てられるまでの待ち時間</a></li>
  <li><a href="#difference_establish_password">村作成パスワード</a></li>
</ul>

<h4 id="difference_max_user">村の最大人数を制限できます</h4>
<div>
<?php Info::OutputMaxUser(); ?>
</div>

<h4 id="difference_active_room">同時稼働できる村の数 [Ver. 1.4.0 α19～]</h4>
<div>
サーバ負荷の調整のため、同時稼働できる村の数をサーバ管理者が設定できます。<br>
現在の設定は [ <?php printf('%d村', RoomConfig::MAX_ACTIVE_ROOM); ?> ] までです。
</div>

<h4 id="difference_establish_wait">次の村を立てられるまでの待ち時間 [Ver. 1.4.0 β1～]</h4>
<div>
打ち合わせミスや、リロードによる多重村立て事故を防ぐため、一つの村が立ってから次の村を立てられるまでの待ち時間をサーバ管理者が設定できます。<br>
現在の設定は [ <?php InfoTime::Output(RoomConfig::ESTABLISH_WAIT); ?> ] です。
</div>

<h4 id="difference_establish_password">村作成パスワード [Ver. 1.3.5～ / Ver. 1.4.2～ / Ver. 1.5.0 α3～]</h4>
<div>
サーバ管理者が設定する事で村作成時にパスワードの入力が必要になります。
</div>

<h3 id="difference_title_time">時間関連</h3>
<ul>
  <li><a href="#difference_real_time">リアルタイム制オプション</a></li>
  <li><a href="#difference_spend_time">非リアルタイム制の会話の時間消費の上限</a></li>
  <li><a href="#difference_silence">強制沈黙</a></li>
  <li><a href="#difference_wait_morning">早朝待機制オプション</a></li>
</ul>

<h4 id="difference_real_time">リアルタイム制オプション</h4>
<div>
村を作成するときに「<a href="game_option.php#real_time">リアルタイム制</a>」にチェックを入れると、ゲーム中の仮想時間 (昼12時間、夜6時間) が発言により消費されるのではなく、固定された実時間で消費されていきます。<br>
設定される時間は村を作成する人が決定することができます
(デフォルト 昼： [ <?php printf('%d分', TimeConfig::DEFAULT_DAY); ?> ]　夜： [ <?php printf('%d分', TimeConfig::DEFAULT_NIGHT); ?> ])。<br>
その村に設定された制限時間を知るには、ゲーム一覧のゲームオプションアイコン、リアルタイム制 <?php Info::OutputRealTime(); ?> にマウスポインタを合わせることで表示されます。
</div>
<h5>Ver. 1.4.0 β4～</h5>
<div>
PC の時計をサーバと合わせる必要がなくなりました。
</div>

<h4 id="difference_spend_time">非リアルタイム制の会話の時間消費の上限</h4>
<div>
半角100文字 (全角50文字) で 昼： [ <?php echo InfoTime::$spend_day; ?> ] 夜：[ <?php echo InfoTime::$spend_night; ?> ] ずつ消費されていきますが、どれだけ文字が増えても最大半角400文字 (全角200文字) までの消費時間までしか増えません。<br>
半角400文字以上で発言しても消費される時間は半角400文字分と同じです。
</div>

<h4 id="difference_silence">強制沈黙</h4>
<div>
非リアルタイム制の場合、誰も発言をせず [ <?php echo InfoTime::$silence; ?> ] 過ぎた場合には強制的に沈黙となり時間が消費されます。<br>
消費される時間は 昼： [ <?php echo InfoTime::$silence_day; ?> ] 夜： [ <?php echo InfoTime::$silence_night; ?> ]です。
</div>

<h4 id="difference_wait_morning">早朝待機制オプション [Ver. 1.4.0 β17～]</h4>
<div>
村を作成するときに「<a href="game_option.php#wait_morning">早朝待機制</a>」にチェックを入れると、夜明け後 [ <?php echo TimeConfig::WAIT_MORNING; ?>秒 ] の間は発言ができません。<br>
これにより、昼の発言開始のタイミングを揃えることができます。
</div>

<h3 id="difference_title_network">外部サーバ連携</h3>
<ul>
  <li><a href="#difference_shared_server">外部村稼動情報</a></li>
  <li><a href="#difference_bbs">掲示板</a></li>
  <li><a href="#difference_twitter">Twitter</a></li>
</ul>

<h4 id="difference_shared_server">外部村稼動情報 [Ver. 1.4.0 α16～]</h4>
<div>
サーバ管理者が設定した外部の人狼式サーバの稼働状況を見ることができます。
</div>

<h4 id="difference_bbs">掲示板 [Ver. 1.4.0 β9～]</h4>
<div>
サーバ管理者が設定した 2ch 互換システムの掲示板の情報を見ることができます。
</div>

<h4 id="difference_twitter">Twitter [Ver. 1.4.0 β9～]</h4>
<div>
サーバ管理者が設定した Twitter アカウントに村立て情報を投稿することができます。
</div>

<h3 id="difference_cache">データキャッシュ [Ver. 2.2.0 α8～]</h3>
<div>
システムの負荷を下げるために一定時間、データのキャッシュを取る事ができます (サーバ管理者設定)。<br>
各個別設定を有効にするには全体設定が有効化されている必要があります。<br>
現在の全体設定は [ <?php printf(CacheConfig::ENABLE ? '有効' : '無効'); ?> ] になっています。<br>
村作成時に [ <?php InfoTime::Output(CacheConfig::EXCEED); ?> ] より前のキャッシュデータは全て削除されます。
</div>
<ul>
  <li><a href="#difference_cache_talk_view">ゲーム内会話 (観戦者用)</a></li>
  <li><a href="#difference_cache_talk_play">ゲーム内会話 (参加者用)</a></li>
  <li><a href="#difference_cache_talk_heaven">霊界会話</a></li>
  <li><a href="#difference_cache_old_log">過去ログ</a></li>
  <li><a href="#difference_cache_old_log_list">過去ログ一覧</a></li>
</ul>

<h4 id="difference_cache_talk_view">ゲーム内会話 (観戦者用) [Ver. 2.2.0 α8～]</h4>
<div>
有効化されると画面内に次回キャッシュ更新時刻が表示されます。<br>
現在の個別設定は [ <?php printf(CacheConfig::ENABLE_TALK_VIEW ? '有効' : '無効'); ?> ] で、キャッシュ時間は [ <?php InfoTime::Output(CacheConfig::TALK_VIEW_EXPIRE); ?> ]、 参加人数が [ <?php echo CacheConfig::TALK_VIEW_COUNT; ?>人 ] から有効になります。<br>
ゲームシーンが更新されるとキャッシュがリセットされます。<br>
</div>
<h5>Ver. 2.2.0 β2～</h5>
<div>
有効化参加人数設定追加<br>
シーン切り替えリセット処理追加
</div>

<h4 id="difference_cache_talk_play">ゲーム内会話 (参加者用) [Ver. 2.2.0 β2～]</h4>
<div>
有効化されると画面内に次回キャッシュ更新時刻が表示されます。<br>
対象シーンはゲーム開始前とゲーム終了後です。<br>
現在の個別設定は [ <?php printf(CacheConfig::ENABLE_TALK_PLAY ? '有効' : '無効'); ?> ] で、キャッシュ時間は [ <?php InfoTime::Output(CacheConfig::TALK_PLAY_EXPIRE); ?> ]、 参加人数が [ <?php echo CacheConfig::TALK_PLAY_COUNT; ?>人 ] から有効になります。<br>
参加者の誰かが発言するか、ゲームシーンが更新されるとリセットされます。
</div>

<h4 id="difference_cache_talk_heaven">霊界会話 [Ver. 2.2.0 β2～]</h4>
<div>
有効化されると画面内に次回キャッシュ更新時刻が表示されます。<br>
現在の個別設定は [ <?php printf(CacheConfig::ENABLE_TALK_HEAVEN ? '有効' : '無効'); ?> ] で、キャッシュ時間は [ <?php InfoTime::Output(CacheConfig::TALK_HEAVEN_EXPIRE); ?> ]、 参加人数が [ <?php echo CacheConfig::TALK_HEAVEN_COUNT; ?>人 ] から有効になります。<br>
参加者の誰かが発言するか、ゲームシーンが更新されるとリセットされます。
</div>

<h4 id="difference_cache_old_log">過去ログ [Ver. 2.2.0 β1～]</h4>
<div>
各村ログの正・逆などのオプション別にキャッシュされます。<br>
現在の個別設定は [ <?php printf(CacheConfig::ENABLE_OLD_LOG ? '有効' : '無効'); ?> ] で、キャッシュ時間は [ <?php InfoTime::Output(CacheConfig::OLD_LOG_EXPIRE); ?> ] です。
</div>

<h4 id="difference_cache_old_log_list">過去ログ一覧 [Ver. 2.2.0 β1～]</h4>
<div>
ページ番号などのオプションが無いページのみがキャッシュされます。<br>
現在の個別設定は [ <?php printf(CacheConfig::ENABLE_OLD_LOG_LIST ? '有効' : '無効'); ?> ] で、キャッシュ時間は [ <?php InfoTime::Output(CacheConfig::OLD_LOG_LIST_EXPIRE); ?> ] です。
</div>

<h3 id="difference_title_limit">制限事項</h3>
<ul>
  <li><a href="#difference_ip">同じ村には同じ IP アドレスで複数登録することはできません</a></li>
  <li><a href="#difference_limit">入村制限</a></li>
  <li><a href="#difference_talk_limit">発言量制限</a></li>
  <li><a href="#difference_escape_talk">半角 \ マークは発言できません</a></li>
  <li><a href="#difference_escape_room">半角 \ マークやシングルクオーテーション ’ は村名やユーザ名には使用できません</a></li>
  <li><a href="#difference_user_name">他の人と同じ名前のユーザ名やハンドルネームは登録できません</a></li>
</ul>

<h4 id="difference_ip">同じ村には同じ IP アドレスで複数登録することはできません</h4>
<div>
多重登録を防ぐために同じ村に同じ IP アドレスで複数登録することはできません。<br>
この機能はスクリプトの設定で有効、無効を設定することができます。<br>
一つのグローバル IP アドレスでルータを用いて複数の人が参加したい場合はサーバ管理者に相談してください。<br>
現在の設定は [ <?php printf('登録%s', GameConfig::LIMIT_IP ? '不可' : '可能'); ?> ] になっています。
</div>

<h4 id="difference_limit">入村制限 [Ver. 1.4.0 β18～]</h4>
<div>
サーバ管理者は IP・ホスト名で入村・村立て制限をすることができます。
</div>
<h5>Ver. 2.2.0 α8～</h5>
<div>
トリップ用のホワイトリスト実装
</div>
<h5>Ver. 2.1.0～</h5>
<div>
村立て制限機能実装
</div>
<h5>Ver. 1.4.0 β19～</h5>
<div>
ホワイトリスト実装
</div>

<h4 id="difference_talk_limit">発言量制限 [Ver. 1.4.15～ / Ver. 1.5.1～ / Ver. 2.0.0 α3～]</h4>
<div>
一定の文字数・行数を超えた発言を入れることはできません。
</div>

<h4 id="difference_escape_talk">半角 &yen; マークは発言できません</h4>
<div>半角 &yen; マークは発言できません、仕様です。</div>

<h4 id="difference_escape_room">半角 &yen; マークやシングルクオーテーション &rsquo; は村名やユーザ名には使用できません</h4>
<div>
半角 &yen; マークやシングルクオーテーション &rsquo; は村名やユーザ名には使用できません、仕様です。<br>
その他サーバの仕様によっては他の記号も使用できない可能性があります、ご了承ください。
</div>

<h4 id="difference_user_name">他の人と同じ名前のユーザ名やハンドルネームは登録できません</h4>
<div>
他の人と同じ名前のユーザ名やハンドルネームは登録できません、仕様です。<br>
同じ名前を狙うのなら、半角数字を全角にしたり工夫してください。
</div>

<h2 id="faq">FAQ (よくある質問と答え)</h2>
<ul>
  <li><a href="#faq_session">セッションエラーと表示されました</a></li>
  <li><a href="#faq_login">ログインするには</a></li>
  <li><a href="#faq_heaven_mode">死亡して天国モードに行く場合に画面がおかしくなる</a></li>
  <li><a href="#faq_dead_icon">死亡者のアイコンにマウスポインタを乗せると画像が異常に大きくなる</a></li>
  <li><a href="#faq_bug">バグを見つけたのですが</a></li>
  <li><a href="#faq_feature">ゲームの機能に関して要望があるのですが</a></li>
  <li><a href="#faq_talk">発言したときに時々発言できてないときがある</a></li>
  <li><a href="#faq_mage_cancel">同じ日の夜に占い師が妖狐を占い、その占い師を人狼が喰い殺した場合どうなるの？</a></li>
  <li><a href="#faq_kick">Kickされたときのユーザ名や村人名は再度同じ村で使用できるの？</a></li>
  <li><a href="#faq_distribution">このスクリプトを勝手に改造して再配布してもいいの？</a></li>
</ul>

<h3 id="faq_session">セッションエラーと表示されました</h3>
<div>
ログインするとセッション情報が Cookie としてブラウザに渡されます。<br>
そのセッション情報でユーザの判別 (ログインの有無、ユーザ名の識別) を行っています。<br>
他のブラウザで多重ログインしたりするとセッションが変わってしまい、前ログインしていたセッションは無効になります。<br>
（1ユーザに1セッション、別のセッションが開始されると前のセッション ID は DB から削除されます）<br>
また Cookie を許可していない場合はセッション情報を持つことが出来ないためログインを維持できません。<br>
Cookie は有効にしてください。
</div>

<h3 id="faq_login">ログインするには</h3>
<div>
村人登録すると自動でセッションが発行され、ログインされます。<br>
そのままトップページに戻っても村のリンクをクリックすれば自動でログインされます。<br>
しかしセッション情報を破棄してしまったり、ブラウザを変えたりすると自動でログインされなくなります。<br>
そのときは観戦ページの上部の「ユーザ名」「パスワード」を入力してログインしてください。<br>
また、ゲームが終了してしまった場合は再度ログインすることは出来ません。
</div>

<h3 id="faq_heaven_mode">死亡して天国モードに行く場合に画面がおかしくなる</h3>
<div>
自動ジャンプは JavaScript で実装されているので、ブラウザ依存でおかしくなっている可能性があります。<br>
Mac では一応対策されているつもりです。<br>
あとタブブラウザでなる場合もあるそうです。<br>
もし画面が変になりましたら再ログインするか、それでもダメなら公式の連絡掲示板に詳しく状況を報告していただければ助かります。<br>
動作確認をしているブラウザはIE8、FireFox、Opera（どれもWin32用）です。
</div>

<h3 id="faq_dead_icon">死亡者のアイコンにマウスポインタを乗せると画像が異常に大きくなる</h3>
<div>
これはアイコン画像をリサイズせずにアップロードしているためにこうなります。<br>
サーバ管理者にリサイズしなおすようにお願いしてみてください。
</div>

<h3 id="faq_bug">バグを見つけたのですが</h3>
<div>
<a href="http://sourceforge.jp/projects/jinrousiki/" target="_blank">SorceForge</a> のバグ報告か、<a href="http://jbbs.livedoor.jp/bbs/read.cgi/netgame/2829/1240771280/" target="_blank">ウミガメ人狼専用掲示板</a>の式神研究同好会スレッドにできるだけ詳しくバグの起きた状況とその内容を報告してください。<br>
できれば対戦ログも教えていただければ助かります。
</div>

<h3 id="faq_feature">ゲームの機能に関して要望があるのですが</h3>
<div>
「<a href="#faq_bug">バグを見つけたのですが</a>」と同じ場所に投稿してください。
実現できるかどうかは分かりませんが・・・
</div>

<h3 id="faq_talk">発言したときに時々発言できてないときがある</h3>
<div>
ゲーム中、昼と夜の切り替わり付近でこのような動作をするときがあります。<br>
これは夜の<a href="rule.php#rule_role_wolf">人狼</a>同士の秘密の会話をしているうちに突然朝になり、うっかり夜に発言するはずだった会話が朝に漏れてしまった、ということが無いようにこのような処理をしています。<br>
昼→夜も同様です。<br>
具体的な処理ですが、最後に読み込んだ状況 (昼、夜、ゲーム前) と書き込んだときの状況が一致してなければ発言しないようになっています。
</div>

<h3 id="faq_mage_cancel">同じ日の夜に占い師が妖狐を占い、その占い師を人狼が喰い殺した場合どうなるの？</h3>
<div>
通常占い師が<a href="rule.php#rule_role_fox">妖狐</a>を占うと占われた<a href="rule.php#rule_role_fox">妖狐</a>は死んでしまいますが、同日に<a href="rule.php#rule_role_wolf">人狼</a>がその占い師を襲うと占いは失敗となり<a href="rule.php#rule_role_fox">妖狐</a>は死なずに済みます。<br>
勝率の低い<a href="rule.php#rule_role_fox">妖狐</a>のバランスを取るためにこのようになっています。
</div>

<h3 id="faq_kick">Kickされたときのユーザ名や村人名は再度同じ村で使用できるの？</h3>
<div>ユーザ名は使用できませんが、村人名は使用できます。</div>

<h3 id="faq_distribution">このスクリプトを勝手に改造して再配布してもいいの？</h3>
<div>
かまいません。<br>
許可を取る必要もありませんし、報告する義務もありません。<br>
しかし、植物の背景画像、左上にある文字の入ったタイトル画像は<a href="http://photozou.jp/photo/list/2066445/5445429" target="_blank">天の欠片</a>さんの素材を使用しています。<br>
この画像をそのまま使う場合は<a href="copyright.php">謝辞・素材</a>の天の欠片さんへのリンクを削除しないようにお願いします。<br>
またこの画像の著作権は天の欠片さんの物なので、自分で撮影したとか自分で作ったとか言わないようにしてください。<br>
Ver. 1.2.0で追加した画像については、<a href="http://azukifont.mints.ne.jp/" target="_blank">あずきふぉんと</a>さんのフォントを利用させていただいています。<br>
この画像をそのまま使う場合は<a href="copyright.php">謝辞・素材</a>のあずきふぉんとさんへのリンクを削除しないようにお願いします。<br>
このシステムには mbstring モジュールに非対応なサーバでも稼動できるように <a href="http://www.matsubarafamily.com/blog/mbemu.php" target="_blank">mbstringエミュレータ</a>が入っています。<br>
<a href="copyright.php">謝辞・素材</a>の mbstring エミュレータさんへのリンクを削除しないようにお願いします。
</div>
</body>
</html>
