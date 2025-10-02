<?php
class MessageImageBuilder {
  public $generator;
  public $list;
  public $color_list = array(
    'human'		=> array('R' =>  96, 'G' =>  96, 'B' =>  96),
    'mage'		=> array('R' => 153, 'G' =>  51, 'B' => 255),
    'necromancer'	=> array('R' =>   0, 'G' => 102, 'B' => 153),
    'medium'		=> array('R' => 153, 'G' => 204, 'B' =>   0),
    'priest'		=> array('R' =>  77, 'G' =>  77, 'B' => 204),
    'guard'		=> array('R' =>  51, 'G' => 153, 'B' => 255),
    'common'		=> array('R' => 204, 'G' => 102, 'B' =>  51),
    'poison'		=> array('R' =>   0, 'G' => 153, 'B' => 102),
    'revive'		=> array('R' =>   0, 'G' => 153, 'B' => 102),
    'assassin'		=> array('R' => 144, 'G' =>  64, 'B' =>  64),
    'mind'		=> array('R' => 160, 'G' => 160, 'B' =>   0),
    'jealousy'		=> array('R' =>   0, 'G' => 204, 'B' =>   0),
    'brownie'		=> array('R' => 144, 'G' => 192, 'B' => 160),
    'wizard'		=> array('R' => 187, 'G' => 136, 'B' => 204),
    'doll'		=> array('R' =>  96, 'G' =>  96, 'B' => 255),
    'escaper'		=> array('R' =>  96, 'G' =>  96, 'B' => 144),
    'wolf'		=> array('R' => 255, 'G' =>   0, 'B' =>   0),
    'fox'		=> array('R' => 204, 'G' =>   0, 'B' => 153),
    'lovers'		=> array('R' => 255, 'G' =>  51, 'B' => 153),
    'quiz'		=> array('R' => 153, 'G' => 153, 'B' => 204),
    'vampire'		=> array('R' => 208, 'G' =>   0, 'B' => 208),
    'chiroptera'	=> array('R' => 136, 'G' => 136, 'B' => 136),
    'ogre'		=> array('R' => 224, 'G' => 144, 'B' =>   0),
    'duelist'		=> array('R' => 240, 'G' =>  80, 'B' => 112),
    'mania'		=> array('R' => 192, 'G' => 160, 'B' =>  96),
    'vote'		=> array('R' => 153, 'G' => 153, 'B' =>   0),
    'chicken'		=> array('R' =>  51, 'G' => 204, 'B' => 255),
    'liar'		=> array('R' => 102, 'G' =>   0, 'B' => 153),
    'decide'		=> array('R' => 153, 'G' => 153, 'B' => 153),
    'authority'		=> array('R' => 102, 'G' => 102, 'B' =>  51),
    'luck'		=> array('R' => 102, 'G' => 204, 'B' => 153),
    'voice'		=> array('R' => 255, 'G' => 153, 'B' =>   0),
    'no_last_words'	=> array('R' => 221, 'G' =>  34, 'B' =>  34),
    'sex_male'		=> array('R' =>   0, 'G' =>   0, 'B' => 255),
    'wisp'		=> array('R' => 170, 'G' => 102, 'B' => 255),
    'step'		=> array('R' => 102, 'G' => 153, 'B' =>  51)
			  );

  function __construct($list, $font) {
    $font = IMAGE_FONT_PATH . $font;
    $size = ($trans = $list == 'WishRoleList') ? 12 : 11;
    $this->generator = new MessageImageGenerator($font, $size, 3, 3, $trans);
    $this->list = new $list();
  }

  function LoadDelimiter($delimiter, $colors) {
    if (! is_array($colors)) $colors = $this->color_list[$colors];
    return new Delimiter($delimiter, $colors['R'], $colors['G'], $colors['B']);
  }

  function AddDelimiter(array $list) {
    foreach ($list['delimiter'] as $delimiter => $colors) {
      $this->generator->AddDelimiter($this->LoadDelimiter($delimiter, $colors));
    }
  }

  function SetDelimiter(array $list) {
    if (isset($list['type'])) $this->SetDelimiter($this->list->{$list['type']});
    if (is_null($list['delimiter'])) $list['delimiter'] = array();
    $this->AddDelimiter($list);
  }

  function Generate($name, $calib = array()) {
    $this->SetDelimiter($this->list->$name);
    return $this->generator->GetImage($this->list->{$name}['message'], $calib);
  }

  function Output($name, $calib = array()) {
    header('Content-Type: image/gif');
    imagegif($this->Generate($name, $calib));
  }

  function Save($name) {
    $image = $this->Generate($name);
    imagegif($image, "./test/{$name}.gif"); //出力先ディレクトリのパーミッションに注意
    imagedestroy($image);
    echo $name . '<br>';
  }

  function Test($name) { $this->Generate($name); }

  //まとめて画像ファイル生成
  function OutputAll() {
    foreach ($this->list as $name => $list) {
      $image = $this->Generate($name);
      imagegif($image, "./test/{$name}.gif"); //出力先ディレクトリのパーミッションに注意
      imagedestroy($image);
      echo $name . '<br>';
    }
  }
}
