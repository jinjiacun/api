<?php /* Smarty version Smarty-3.1.13, created on 2016-04-08 13:01:44
         compiled from "/var/www/html/testlink/gui/templates/mainPage.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12526423085707abb8f3c0f2-55925682%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0d9e610874403621f01823333e40407c7d99756c' => 
    array (
      0 => '/var/www/html/testlink/gui/templates/mainPage.tpl',
      1 => 1427533465,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12526423085707abb8f3c0f2-55925682',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'cfg_section' => 0,
    'basehref' => 0,
    'gui' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_5707abb909ef69_60573961',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5707abb909ef69_60573961')) {function content_5707abb909ef69_60573961($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_replace')) include '/var/www/html/testlink/third_party/smarty3/libs/plugins/modifier.replace.php';
?>
<?php $_smarty_tpl->tpl_vars['cfg_section'] = new Smarty_variable(smarty_modifier_replace(basename($_smarty_tpl->source->filepath),".tpl",''), null, 0);?>
<?php  $_config = new Smarty_Internal_Config("input_dimensions.conf", $_smarty_tpl->smarty, $_smarty_tpl);$_config->loadConfigVars($_smarty_tpl->tpl_vars['cfg_section']->value, 'local'); ?>
<?php echo $_smarty_tpl->getSubTemplate ("inc_head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('popup'=>"yes",'openHead'=>"yes"), 0);?>


<?php echo $_smarty_tpl->getSubTemplate ("inc_ext_js.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<script language="JavaScript" src="<?php echo $_smarty_tpl->tpl_vars['basehref']->value;?>
gui/niftycube/niftycube.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload=function()
{

  /* with typeof display_left_block_1 I'm checking is function exists */
  if( typeof display_left_block_1 != 'undefined')
  {
    display_left_block_1();
  }

  if( typeof display_left_block_2 != 'undefined')
  {
    display_left_block_2();
  }

  if( typeof display_left_block_3 != 'undefined')
  {
    display_left_block_3();
  }
    
  if( typeof display_left_block_4 != 'undefined')
  {
    display_left_block_4();
  }

  if( typeof display_left_block_5 != 'undefined')
  {
    display_left_block_5();
  }

  if( typeof display_right_block_1 != 'undefined')
  {
    display_right_block_1();
  }

  if( typeof display_right_block_2 != 'undefined')
  {
    display_right_block_2();
  }

  if( typeof display_right_block_3 != 'undefined')
  {
    display_right_block_3();
  }
   
}
</script>
</head>

<body>
<?php if ($_smarty_tpl->tpl_vars['gui']->value->securityNotes){?>
  <?php echo $_smarty_tpl->getSubTemplate ("inc_msg_from_array.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('array_of_msg'=>$_smarty_tpl->tpl_vars['gui']->value->securityNotes,'arg_css_class'=>"warning"), 0);?>

<?php }?>


<?php echo $_smarty_tpl->getSubTemplate ("mainPageRight.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>



<?php echo $_smarty_tpl->getSubTemplate ("mainPageLeft.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</body>
</html><?php }} ?>
