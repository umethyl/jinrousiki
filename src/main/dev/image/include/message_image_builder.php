<?php
class MessageImageBuilder {
  public $generator;
  public $list;
  public $color_list = [
    'human'		=> ['R' =>  96, 'G' =>  96, 'B' =>  96],
    'mage'		=> ['R' => 153, 'G' =>  51, 'B' => 255],
    'necromancer'	=> ['R' =>   0, 'G' => 102, 'B' => 153],
    'medium'		=> ['R' => 153, 'G' => 204, 'B' =>   0],
    'priest'		=> ['R' =>  77, 'G' =>  77, 'B' => 204],
    'guard'		=> ['R' =>  51, 'G' => 153, 'B' => 255],
    'common'		=> ['R' => 204, 'G' => 102, 'B' =>  51],
    'poison'		=> ['R' =>   0, 'G' => 153, 'B' => 102],
    'revive'		=> ['R' =>   0, 'G' => 153, 'B' => 102],
    'assassin'		=> ['R' => 144, 'G' =>  64, 'B' =>  64],
    'mind'		=> ['R' => 160, 'G' => 160, 'B' =>   0],
    'jealousy'		=> ['R' =>   0, 'G' => 204, 'B' =>   0],
    'brownie'		=> ['R' => 144, 'G' => 192, 'B' => 160],
    'wizard'		=> ['R' => 187, 'G' => 136, 'B' => 204],
    'doll'		=> ['R' =>  96, 'G' =>  96, 'B' => 255],
    'escaper'		=> ['R' =>  96, 'G' =>  96, 'B' => 144],
    'wolf'		=> ['R' => 255, 'G' =>   0, 'B' =>   0],
    'fox'		=> ['R' => 204, 'G' =>   0, 'B' => 153],
    'lovers'		=> ['R' => 255, 'G' =>  51, 'B' => 153],
    'quiz'		=> ['R' => 153, 'G' => 153, 'B' => 204],
    'vampire'		=> ['R' => 208, 'G' =>   0, 'B' => 208],
    'chiroptera'	=> ['R' => 136, 'G' => 136, 'B' => 136],
    'ogre'		=> ['R' => 224, 'G' => 144, 'B' =>   0],
    'duelist'		=> ['R' => 240, 'G' =>  80, 'B' => 112],
    'tengu'		=> ['R' => 192, 'G' =>  48, 'B' =>  48],
    'mania'		=> ['R' => 192, 'G' => 160, 'B' =>  96],
    'vote'		=> ['R' => 153, 'G' => 153, 'B' =>   0],
    'chicken'		=> ['R' =>  51, 'G' => 204, 'B' => 255],
    'liar'		=> ['R' => 102, 'G' =>   0, 'B' => 153],
    'decide'		=> ['R' => 153, 'G' => 153, 'B' => 153],
    'authority'		=> ['R' => 102, 'G' => 102, 'B' =>  51],
    'luck'		=> ['R' => 102, 'G' => 204, 'B' => 153],
    'voice'		=> ['R' => 255, 'G' => 153, 'B' =>   0],
    'no_last_words'	=> ['R' => 221, 'G' =>  34, 'B' =>  34],
    'sex_male'		=> ['R' =>   0, 'G' =>   0, 'B' => 255],
    'wisp'		=> ['R' => 170, 'G' => 102, 'B' => 255],
    'step'		=> ['R' => 102, 'G' => 153, 'B' =>  51]
  ];

  public function __construct($list, $font) {
    $font  = IMAGE_FONT_PATH . $font;
    $trans = $list == 'WishRoleList';
    $size  = $trans ? 12 : 11;
    $this->generator = new MessageImageGenerator($font, $size, 3, 3, $trans);
    $this->list      = new $list();
  }

  public function LoadDelimiter($delimiter, $colors) {
    if (false === is_array($colors)) {
      $colors = $this->color_list[$colors];
    }
    return new Delimiter($delimiter, $colors['R'], $colors['G'], $colors['B']);
  }

  public function AddDelimiter(array $list) {
    foreach ($list['delimiter'] as $delimiter => $colors) {
      $this->generator->AddDelimiter($this->LoadDelimiter($delimiter, $colors));
    }
  }

  public function SetDelimiter(array $list) {
    if (isset($list['type'])) {
      $this->SetDelimiter($this->list->{$list['type']});
    }
    if (false === isset($list['delimiter'])) {
      $list['delimiter'] = [];
    }
    $this->AddDelimiter($list);
  }

  public function Generate($name, $calib = []) {
    $this->SetDelimiter($this->list->$name);
    return $this->generator->GetImage($this->list->{$name}['message'], $calib);
  }

  public function Output($name, $calib = []) {
    header('Content-Type: image/gif');
    imagegif($this->Generate($name, $calib));
  }

  public function Save($name) {
    $image = $this->Generate($name);
    imagegif($image, "./test/{$name}.gif"); //出力先ディレクトリのパーミッションに注意
    imagedestroy($image);
    echo $name . '<br>';
  }

  public function Test($name) {
    $this->Generate($name);
  }

  //まとめて画像ファイル生成
  public function OutputAll() {
    foreach ($this->list as $name => $list) {
      $image = $this->Generate($name);
      imagegif($image, "./test/{$name}.gif"); //出力先ディレクトリのパーミッションに注意
      imagedestroy($image);
      echo $name . '<br>';
    }
  }
}
