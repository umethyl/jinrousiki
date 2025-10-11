function GameAsync(params, status) {
  var base_href = location.protocol + '//' + location.host + location.pathname.replace(/\/[^/]+$/, '/');
  this.generateHref = function (path) {
    return base_href + path;
  };
  this.params = params;
  this.status = status;
  if (DOMParser) {
    var parser = new DOMParser();
    this.parseHtml = function (text) {
      return parser.parseFromString(text, 'text/html');
    };
  }
  else if (ActiveXObject) {
    this.parseHtml = function (text) {
      var doc = new ActiveXObject('htmlfile');
      doc.open();
      doc.write(text);
      doc.close();
      return doc;
    };
  }
}

function createXHR() {
  if (window.XMLHttpRequest) {
    return new XMLHttpRequest();
  }
  else {
    try {
      return new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      try {
        return new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (_e) {
        return null;
      }
    }
  }
} 

function replaceSection(content, className, isContainer) {
  var newer = content.getElementsByClassName(className);
  var older = document.getElementsByClassName(className);
  if (0 < older.length && 0 < newer.length) {
    var _older = isContainer ? older[0].firstChild : older[0];
    var _newer = isContainer ? newer[0].firstChild : newer[0];
    _older.parentNode.replaceChild(_newer, _older);
  }
}

GameAsync.prototype.Start = function() {
  if (createXHR() == null) {
    location.href = this.status.login
      ? this.generateHref('game_frame.php?room_no=' + params.room_no)
      : this.generateHref('game_view.php?room_no=' + params.room_no);
    return false;
  }
  if (0 < this.params.auto_reload) {
    var self = this;
    setInterval(function () { self.updateTalk(); }, this.params.auto_reload * 1000);
  }
  return true;
};

GameAsync.prototype.updateTalk = function() {
  var url = this.generateHref('async.php?');
  for (var name in this.params) {
    url += '&' + name + '=' + this.params[name];
  }
  var self = this;
  var xhr = createXHR();
  xhr.open('GET', url);
  xhr.setRequestHeader('Cache-Control', 'no-cache');
  xhr.onreadystatechange = function () {
    if (xhr.readyState != 4) {
      return;
    }
    else if (xhr.status != 200) {
      alert("HTTP " + xhr.status + "が発生しました。");
      return;
    }
    self.endUpdateTalk(xhr.responseText);
  };
  xhr.send('');
};

GameAsync.prototype.endUpdateTalk = function(response) {
  var content = this.parseHtml(response);

  //ゲーム進行の更新
  var statusNodes = content.getElementsByClassName('status');
  if (0 < statusNodes.length) {
    var status = {};
    for (var i = 0; i < statusNodes.length; i++) {
      var node = statusNodes[i];
      status[node.id] = node.innerHTML;
    }
    this.updateStatus(status);
  }

  //会話の更新
  replaceSection(content, 'talk');
  //時間通知の更新
  replaceSection(content, 'timelimit');
  //プレーヤーリストの更新
  replaceSection(content, 'player', true);
  //投票テーブルの更新
  replaceSection(content, 'vote-elements');
  //役職通知の更新
  replaceSection(content, 'ability-elements');

  //音声の更新
  var oldSounds = document.getElementsByTagName('object');
  for (var i = 0; i < oldSounds.length; i++) {
    oldSounds[i].parentNode.removeChild(oldSounds[i]);
  }
  var sounds = content.getElementsByTagName('object');
  for (var i = 0; i < sounds.length; i++) {
    document.body.appendChild(sounds[i]);
  }

  //遺言のシャッフル
  var lastwords = document.getElementsByClassName('lastwords');
  if (0 < lastwords.length) {
    var _container = lastwords[0];
    var rows = _container.getElementsByTagName('tr');
    var source = [];
    for (var i = 0; i < rows.length; i++) {
      source[i] = { key: Math.random(), value: rows[i] };
    }
    source.sort(function (a, b) { return a.key - b.key; });
    for (var i = 0; i < source.length; i++) {
      var e = source[i];
      _container.appendChild(e.value);
    }
  }
};

GameAsync.prototype.updateStatus = function(data) {
  if (this.status.scene != data.scene) {
    var reloadTarget = (typeof change_css === 'function') && change_css(data.scene)
      ? window.window
      : window.top;
    reloadTarget.location.assign(reloadTarget.location.href);
  }
  if (typeof updateEndDate === 'function') {
    updateEndDate(data.end_date);
  }
};

var gameasync = null;

function game_async(params, status) {
  gameasync = new GameAsync(params, status);
  gameasync.Start();
}

