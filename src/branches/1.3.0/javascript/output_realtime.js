function output_realtime(){
  var i, seconds, time, value;

  var i = start_time.split(",");
  var base = new Date(i[0], i[1] - 1, i[2], i[3], i[4], i[5]);

  i = end_time.split(",");
  var end  = new Date(i[0], i[1] - 1, i[2], i[3], i[4], i[5]);
  var diff = Math.floor(end - base) / 1000;
  var left = end - new Date();

  if(left > 0){
    var left_time = new Date(0, 0, 0, 0, 0, Math.floor(left / 1000));
    seconds = Math.floor(12 * 60 * 60 / diff * left / 1000);
    time = new Date(0, 0, 0, 0, 0, seconds);
    value = realtime_message;
    if(time.getHours()   > 0){value += time.getHours()   + "時間";}
    if(time.getMinutes() > 0){value += time.getMinutes() + "分";}
    value += "(実時間 ";
    if(left_time.getMinutes() > 0){value += left_time.getMinutes() + "分";}
    if(left_time.getSeconds() > 0){value += left_time.getSeconds() + "秒";}
    value += ")";
  }
  else{
    seconds = Math.abs(Math.floor(left/1000));
    time = new Date(0, 0, 0, 0, 0, seconds);
    value = "超過時間 ";
    if(time.getHours()   > 0){value += time.getHours()   + "時間";}
    if(time.getMinutes() > 0){value += time.getMinutes() + "分";}
    if(time.getSeconds() > 0){value += time.getSeconds() + "秒";}
  }
  document.realtime_form.output_realtime.value = value;
  tid = setTimeout("output_realtime()", 1000);
}
