<?php
use GDO\Facebook\GDT_FBAuthButton;
use GDO\UI\GDT_Button;

$field instanceof GDT_FBAuthButton;
?>
<?php $icon = sprintf('<img src="GDO/Facebook/img/fb-btn.png" title="%s" style="width: 300px;" />', t('btn_continue_with_fb')); ?>
<?= GDT_Button::make()->noLabel()->href($field->href)->rawIcon($icon); ?>
