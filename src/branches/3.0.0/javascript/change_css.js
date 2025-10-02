function change_css(scene) {
  var e = parent.up.document.getElementById('scene');
  if (e) {
    e.href = 'css/game_' + scene + '.css';
    return true;
  }
  return false;
}
