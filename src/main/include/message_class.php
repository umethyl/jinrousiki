<?php
// システムメッセージ格納クラス
class Message{
  //-- room_manger.php --//
  //CreateRoom() : 村作成
  //身代わり君のコメント
  var $dummy_boy_comment = '僕はおいしくないよ';

  //身代わり君の遺言
  var $dummy_boy_last_words = '僕はおいしくないって言ったのに……';

  //-- user_manager.php --//
  //EntryUser() : ユーザ登録
  //入村メッセージ
  var $entry_user = 'さんが村の集会場にやってきました';

  //-- game_view.php & OutputGameHTMLHeader() --//
  var $vote_announce = '時間がありません。投票してください'; //会話の制限時間切れ

  //-- game_functions.php --//
  //OutputVictory() : 村・本人の勝敗結果
  //村人勝利
  var $victory_human = '[村人勝利] 村人たちは人狼の血を根絶することに成功しました';

  //人狼・狂人勝利
  var $victory_wolf = '[人狼・狂人勝利] 最後の一人を食い殺すと人狼達は次の獲物を求めて村を後にした';

  //妖狐勝利 (村人勝利版)
  var $victory_fox1 = '[妖狐勝利] 人狼がいなくなった今、我の敵などもういない';

  //妖狐勝利 (人狼勝利版)
  var $victory_fox2 = '[妖狐勝利] マヌケな人狼どもを騙すことなど容易いことだ';

  //恋人・キューピッド勝利
  var $victory_lovers = '[恋人・キューピッド勝利] 愛の前には何者も無力だったのでした';

  //引き分け
  var $victory_draw = '[引き分け] 引き分けとなりました';

  //全滅
  var $victory_vanish = '[引き分け] そして誰も居なくなった……';

  //廃村
  var $victory_none = '過疎が進行して人がいなくなりました';

  var $win  = 'あなたは勝利しました'; //本人勝利
  var $lose = 'あなたは敗北しました'; //本人敗北
  var $draw = '引き分けとなりました'; //引き分け

  //OutputReVoteList() : 再投票アナウンス
  var $revote = '再投票となりました'; //投票結果
  var $draw_announce = '再投票となると引き分けになります'; //引き分け告知

  //OutputTalkLog() : 会話、システムメッセージ出力
  var $objection = 'が「異議」を申し立てました'; //異議あり
  //var $game_start = 'はゲーム開始に投票しました' //ゲーム開始投票 //現在は不使用
  var $kick_do  = 'に KICK 投票しました'; //KICK 投票
  var $vote_do  = 'に処刑投票しました';   //処刑投票
  var $wolf_eat = 'に狙いをつけました';   //人狼の投票
  var $mage_do  = 'を占います';           //占い師の投票
  var $guard_do = 'の護衛に付きました';   //狩人の投票
  var $cupid_do = 'に愛の矢を放ちました'; //キューピッドの投票

  var $morning_header = '朝日が昇り'; //朝のヘッダー
  var $morning_footer = '日目の朝がやってきました'; //朝のフッター
  var $night = '日が落ち、暗く静かな夜がやってきました'; //夜
  var $dummy_boy = '身代わり君：'; //仮想GMモード用ヘッダー

  var $wolf_howl = 'アオォーン・・・'; //狼の遠吠え
  var $common_talk = 'ヒソヒソ・・・'; //共有者の小声

  //OutputLastWords() : 遺言の表示
  var $lastwords = '夜が明けると前の日に亡くなった方の遺言書が見つかりました';

  //OutoutDeadManType() : 死因の表示
  var $deadman         = 'は無残な姿で発見されました'; //全員に表示されるメッセージ
  var $wolf_killed     = 'は狼の餌食になったようです'; //狼の襲撃
  var $fox_dead        = '(妖狐) は占い師に呪い殺されたようです'; //狐呪殺
  var $poison_dead     = 'は毒に冒され死亡したようです'; //埋毒者の道連れ
  var $vote_killed     = 'は投票の結果処刑されました'; //吊り
  var $lovers_followed = 'は恋人の後を追い自殺しました'; //恋人の後追い自殺

  //OutputAbility() : 能力の表示
  var $ability_dead     = 'アナタは息絶えました・・・';     //死んでいる場合
  var $ability_wolf_eat = '喰い殺す人を選択してください';   //狼の投票
  var $ability_mage_do  = '占う人を選択してください';       //占い師の投票
  var $ability_guard_do = '護衛する人を選択してください';   //狩人の投票
  var $ability_cupid_do = '結びつける二人を選んでください'; //キューピッドの投票

  //-- game_play.php --//
  //CheckSilence()
  var $silence = 'ほどの沈黙が続いた'; //沈黙で時間経過 (会話で時間経過制)
  //突然死の警告メッセージ
  var $sudden_death_announce = '投票完了されない方は死して地獄へ堕ちてしまいます';
  var $sudden_death = 'さんは突然お亡くなりになられました'; //突然死

  //投票リセット
  var $vote_reset = '＜投票がリセットされました　再度投票してください＞';

  //-- game_vote.php --//
  //Kick で村から去った人
  var $kick_out = 'さんは席をあけわたし、村から去りました';

  //OutputVoteBeforeGame()
  var $submit_kick_do    = '対象をキックするに一票'; //Kick 投票ボタン
  var $submit_game_start = 'ゲームを開始するに一票'; //ゲーム開始ボタン

  //OutputVoteDay()
  var $submit_vote_do = '対象を処刑するに一票'; //処刑投票ボタン

  //OutputVoteNight()
  var $submit_wolf_eat = '対象を喰い殺す (先着)'; //狼の襲撃ボタン
  var $submit_mage_do  = '対象を占う'; //占い師の投票ボタン
  var $submit_guard_do = '対象を護衛する'; //狩人の投票ボタン
  var $submit_cupid_do = '対象に愛の矢を放つ'; //キューピッドの投票ボタン
}
?>
