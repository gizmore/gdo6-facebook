<?php
use GDO\Facebook\GDT_FBAuthButton;
use GDO\UI\GDT_Link;
$field instanceof GDT_FBAuthButton;
$icon = sprintf('<img src="GDO/Facebook/img/fb-btn.png" title="%s" style="width: 300px;" />', t('btn_continue_with_fb'));

echo GDT_Link::make()->noLabel()->href($field->href)->rawIcon($icon)->render();
