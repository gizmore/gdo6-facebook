<?php
use GDO\Facebook\GDO_FBAuthButton;
use GDO\UI\GDO_Button;

$field instanceof GDO_FBAuthButton;
?>
<?php $icon = sprintf('<img src="GDO/Facebook/img/fb-btn.png" title="%s" style="width: 300px;" />', t('btn_continue_with_fb')); ?>
<?= GDO_Button::make()->noLabel()->href($field->href)->rawIcon($icon); ?>
