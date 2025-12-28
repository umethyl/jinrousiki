<?php
class AbilityList extends RoleMessageList {
  public $ability_scripter = [
    'message' => "　あなたは有名になったので、|処刑|_投票数_が +1 されます。",
    'delimiter' => ['|' => 'vote', '_' => 'authority']];

  public $ability_eccentricer = [
    'message' => "　あなたは傾奇納めしたので、|処刑|_投票数_は増えません。",
    'delimiter' => ['|' => 'vote', '_' => 'authority']];

  public $ability_poison = [
    'message' => "　あなたは|毒|を持っています。#処刑#されたり、_人狼_に襲撃された時に誰か一人を道連れにします。",
    'delimiter' => ['|' => 'poison', '#' => 'vote', '_' => 'wolf']];

  public $ability_ascetic_1 = ['message' => '臨',                   'type' => 'wolf'];
  public $ability_ascetic_2 = ['message' => '臨兵',                 'type' => 'wolf'];
  public $ability_ascetic_3 = ['message' => '臨兵|闘|',             'type' => 'wolf'];
  public $ability_ascetic_4 = ['message' => '臨兵|闘|者',           'type' => 'wolf'];
  public $ability_ascetic_5 = ['message' => '臨兵|闘|者皆',         'type' => 'wolf'];
  public $ability_ascetic_6 = ['message' => '臨兵|闘|者皆|陣|',     'type' => 'wolf'];
  public $ability_ascetic_7 = ['message' => '臨兵|闘|者皆|陣|列',   'type' => 'wolf'];
  public $ability_ascetic_8 = ['message' => '臨兵|闘|者皆|陣|列在', 'type' => 'wolf'];
  public $ability_ascetic_9 = ['message' => '|臨兵闘者皆陣列在前|', 'type' => 'wolf'];

  public $ability_awake_wizard = [
    'message' => "　あなたは#人狼#の襲撃耐性を失いましたが、代わりに強力な|魔法|を使うことができます。",
    'delimiter' => ['|' => 'wizard', '#' => 'wolf']];

  public $ability_trap_wolf = [
    'message' => "　|罠|の設置が完了しました。",
    'type' => 'wolf'];

  public $ability_step_wolf = [
    'message' => "残りステルス|襲撃|回数：",
    'type' => 'wolf'];

  public $ability_sirius_wolf = [
    'message' => "　残りの|狼|が二人になりました。人の繰り出す業 (#暗殺#・|罠|) は、もはやあなたを貫けません。",
    'type' => 'sirius_wolf'];

  public $ability_full_sirius_wolf = [
    'message' => "　あなたが最後の|狼|です。今や天に輝く|狼|となったあなたに、噛めないものはあんまりない。",
    'type' => 'sirius_wolf'];

  public $ability_possessed_mad = [
    'message' => "　あなたは呪詛が満ちたので、|処刑|_投票数_が +1 されます。",
    'delimiter' => ['|' => 'vote', '_' => 'authority']];

  public $common_partner = [
    'message' => "同じ|共有者|の仲間は以下の人たちです： ",
    'delimiter' => ['|' => 'common']];

  public $mind_scanner_target = [
    'message' => "あなたが|心を読んでいる|のは以下の人たちです： ",
    'type' => 'mind_read'];

  public $mind_friend_list = [
    'message' => "あなたと|共鳴|しているのは以下の人たちです： ",
    'type' => 'mind_read'];

  public $servant_target = [
    'message' => "あなたの|主|は以下の人たちです： ",
    'delimiter' => ['|' => 'servant']];

  public $doll_master_list = [
    'message' => "あなたを呪縛する|人形遣い|は以下の人たちです： ",
    'delimiter' => ['|' => 'doll']];

  public $doll_partner = [
    'message' => "|人形遣い|打倒を目指す同志は以下の人たちです： ",
    'type' => 'doll_master_list'];

  public $wolf_partner = [
    'message' => "誇り高き|人狼|の血を引く仲間は以下の人たちです： ",
    'delimiter' => ['|' => 'wolf']];

  public $mad_partner = [
    'message' => "|人狼|に仕える|狂人|は以下の人たちです： ",
    'type' => 'wolf_partner'];

  public $unconscious_list = [
    'message' => "以下の人たちが|無意識|に歩き回っているようです： ",
    'delimiter' => ['|' => 'human']];

  public $fox_partner = [
    'message' => "深遠なる|妖狐|の智を持つ同胞は以下の人たちです： ",
    'delimiter' => ['|' => 'fox']];

  public $child_fox_partner = [
    'message' => "|妖狐|に与する仲間は以下の人たちです： ",
    'type' => 'fox_partner'];

  public $depraver_partner = [
    'message' => "深遠なる|妖狐|の智を持つ主は以下の人たちです： ",
    'type' => 'fox_partner'];

  public $depraver_no_fox = [
    'message' => "　この村にはあなたの主となる|妖狐|が居ません。生き残ることが勝利条件となります。",
    'type' => 'fox_partner'];

  public $cupid_pair = [
    'message' => "あなたが|愛の矢|を放ったのは以下の人たちです： ",
    'delimiter' => ['|' => 'lovers']];

  public $partner_header = ['message' => "あなたは"];

  public $lovers_footer = [
    'message' => "と|愛し合って|います。妨害する者は誰であろうと消し、二人の愛の世界を築くのです！",
    'type' => 'cupid_pair'];

  public $fake_lovers_footer = [
    'message' => "の|愛人|です。本当に愛されているかどうかは分かりません。",
    'type' => 'cupid_pair'];

  public $quiz_chaos = [
    'message' => "　闇鍋モードではあなたの最大の能力である|人狼|の襲撃に対する耐性がありません。\n　はっきり言って無理ゲーなので好き勝手にクイズでも出して遊ぶと良いでしょう。",
    'delimiter' => ['|' => 'wolf']];

  public $infected_list = [
    'message' => "あなたの血に|感染|したのは以下の人たちです： ",
    'delimiter' => ['|' => 'vampire']];

  public $psycho_infected_list = [
    'message' => "以下の人たちが|洗脳|されているようです： ",
    'delimiter' => ['|' => 'vampire']];

  public $duelist_pair = [
    'message' => "あなたが|宿敵|同士に選んだのは以下の人たちです： ",
    'delimiter' => ['|' => 'duelist']];

  public $rival_footer = [
    'message' => "と|宿敵|同士です。全て倒し、生き残ることが勝利条件に追加されます。",
    'type' => 'duelist_pair'];

  public $avenger_target = [
    'message' => "あなたの|仇敵|は以下の人たちです： ",
    'delimiter' => ['|' => 'duelist']];

  public $patron_target = [
    'message' => "あなたの|受援者|は以下の人たちです： ",
    'delimiter' => ['|' => 'duelist']];

  public $shepherd_patron_list = [
    'message' => "あなたを見守る|羊飼い|は以下の人たちです： ",
    'delimiter' => ['|' => 'duelist']];
}
