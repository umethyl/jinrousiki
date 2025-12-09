function start_auto_play() {
  if (scene_list.length > 0) {
    var scene = scene_list.pop();
    auto_play_open_scene(scene);
    auto_play_interval_open(scene);
  } else {
    auto_play_finish();
    return;
  }
}

function auto_play_open_scene(scene) {
  var stack = talk_id_list[scene];
  for (var id in stack) {
    auto_play_hide('talk_' + stack[id]);
  }

  if (scene + '_day' in talk_id_list) {
    stack = talk_id_list[scene + '_day'];
    for (id in stack) {
      auto_play_hide('talk_' + stack[id]);
    }
  }
  auto_play_open(scene);
}

function auto_play_interval_open(scene) {
  auto_play_open_dead_day(scene);
}

function auto_play_open_dead_day(scene) {
  if (auto_play_open('dead_' + scene + '_day')) {
    setTimeout(function (){ auto_play_open_lastwords(scene); }, 1500);
  } else {
    auto_play_open_lastwords(scene);
  }
}

function auto_play_open_lastwords(scene) {
  if (auto_play_open('lastwords_' + scene + '_day')) {
    setTimeout(function (){ auto_play_interval_open_talk_day(scene); }, 1500);
  } else {
    auto_play_interval_open_talk_day(scene);
  }
}

function auto_play_interval_open_talk_day(scene) {
  var scene_day = scene + '_day';
  var stack = talk_id_list[scene_day];
  if (stack) {
    auto_play_open(scene_day);
  } else {
    return auto_play_open_dead_night(scene);
  }

  var id = stack.pop();
  if (id) {
    auto_play_open('talk_' + id);
    var delay = talk_time_list[scene_day].pop();
    setTimeout(function (){ auto_play_interval_open_talk_day(scene); }, delay);
  } else {
    auto_play_open_vote(scene);
  }
}

function auto_play_open_vote(scene) {
  if (auto_play_open('vote_' + scene)) {
    setTimeout(function (){ auto_play_open_dead_night(scene); }, 1500);
  } else {
    auto_play_open_dead_night(scene);
  }
}

function auto_play_open_dead_night(scene) {
  if (auto_play_open('dead_' + scene)) {
    setTimeout(function (){ auto_play_interval_open_talk(scene); }, 1500);
  } else {
    auto_play_interval_open_talk(scene);
  }
}

function auto_play_interval_open_talk(scene) {
  if (scene in talk_id_list && talk_id_list[scene].length > 0) {
    var id = talk_id_list[scene].pop();
    auto_play_open('talk_' + id);
    setTimeout(function (){ auto_play_interval_open_talk(scene); }, talk_time_list[scene].pop());
  } else {
    setTimeout(function (){ auto_play_hide_scene(scene); }, 1500);
  }
}

function auto_play_hide_scene(scene) {
  if (scene_list.length > 0) {
    auto_play_hide(scene);
    auto_play_hide(scene + '_day');
    auto_play_hide('dead_' + scene + '_day');
    auto_play_hide('dead_' + scene);
    auto_play_hide('lastwords_' + scene + '_day');
    auto_play_hide('vote_' + scene);
    setTimeout('start_auto_play()', 1500);
  } else {
    auto_play_open('winner');
    auto_play_finish();
  }
}

function auto_play_finish() {
  auto_play_open('auto_play_end');
}

function auto_play_open(id) {
  if (document.getElementById(id)) {
    document.getElementById(id).style.display = "block";
    return true;
  } else {
    return false;
  }
}

function auto_play_hide(id) {
  if (document.getElementById(id)) {
    document.getElementById(id).style.display = "none";
    return true;
  } else {
    return false;
  }
}
