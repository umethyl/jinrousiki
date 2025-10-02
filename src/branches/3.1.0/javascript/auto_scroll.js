function auto_scroll() {
  scrollTo(0, y += distance);
  if (y < document.documentElement.scrollHeight) {
    setTimeout('auto_scroll()', timeout);
  }
}
