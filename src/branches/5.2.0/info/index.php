<?php
require_once('init.php');
FrameHTML::OutputHeader(Text::QuoteBracket(InfoMessage::TITLE_TOP));
InfoHTML::OutputFrame('script_info');
FrameHTML::OutputFooter();
