<?php
/*
  ◆虹色迷彩 (rainbow)
  ○仕様
  ・変換リスト：虹 (循環置換)
*/
RoleLoader::LoadFile('passion');
class Role_rainbow extends Role_passion {
  public $convert_say_list = [
    '赤' => '橙', '橙' => '黄', '黄' => '緑', '緑' => '青',
    '青' => '藍', '藍' => '紫', '紫' => '赤'
  ];
}
