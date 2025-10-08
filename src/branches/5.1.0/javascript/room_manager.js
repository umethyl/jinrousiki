/* ◆村作成画面制御関数 */
/* getElementsByClass() 代替関数 */
function get_option_elements(target) {
  if (document.all) {
    var all = document.all;
  } else {
    var all = document.getElementsByTagName("*");
  }

  var list = new Array();
  for (var i = 0, j = 0; i < all.length; i++) {
    if (all[i].className == target) {
      list[j] = all[i];
      j++;
    }
  }
  return list;
}

/* オプション表示切替 */
function change_option_display(target, value) {
  var list = get_option_elements(target);
  for (var i = 0; i < list.length; i++) {
    list[i].style.display = value;
  }
}

/* オプショングループ表示切替 */
function toggle_option_display(target, flag) {
  if (flag) {
    var element = document.getElementById(target + '_on');
    if (! element) {
      return;
    }
    element.style.display = '';
    document.getElementById(target + '_off').style.display = '';
    change_option_display(target, 'none');
  } else {
    var element = document.getElementById(target + '_off');
    if (! element) {
      return;
    }
    element.style.display = 'none';
    document.getElementById(target + '_on').style.display = '';
    change_option_display(target, '');
  }
}

/* 村人置換村連動オプション制御 */
function change_replace_human() {
  var element = document.getElementById('replace_human_selector');

  switch (element.value) {
  case 'full_cupid':
    var target = document.getElementById('cupid');
    if (target) {
      target.checked = false;
    }
    break;

  case 'full_mania':
    var target = document.getElementById('mania');
    if (target) {
      target.checked = false;
    }
    break;
  }
}

/* キューピッド置換村連動オプション制御 */
function change_change_cupid() {
  var element = document.getElementById('change_cupid_selector');

  if (element.value) {
    var target = document.getElementById('cupid');
    if (target) {
      target.checked = false;
    }
  }
}

/* 特殊配役モード連動オプション制御 */
function change_special_role() {
  var element = document.getElementById('special_role');

  if (element.value) {
    toggle_option_display('add_role', true);
  } else {
    toggle_option_display('add_role', false);
  }

  switch (element.value) {
  case 'chaos':
  case 'chaosfull':
  case 'chaos_hyper':
  case 'chaos_verso':
    change_option_display('chaos', '');
    break;

  default:
    change_option_display('chaos', 'none');
    break;
  }
}
