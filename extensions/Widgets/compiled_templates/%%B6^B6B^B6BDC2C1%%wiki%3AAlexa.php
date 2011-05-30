<?php /* Smarty version 2.6.18-dev, created on 2011-05-30 10:25:29
         compiled from wiki:Alexa */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'wiki:Alexa', 2, false),array('modifier', 'default', 'wiki:Alexa', 2, false),array('modifier', 'validate', 'wiki:Alexa', 2, false),)), $this); ?>
<!-- Alexa Graph Widget from http://www.alexa.com/siteowners/widgets/graph -->
<a href="http://www.alexa.com/siteinfo/<?php $_from = $this->_tpl_vars['site']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['s']):
?>+<?php echo ((is_array($_tmp=$this->_tpl_vars['s'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
<?php endforeach; endif; unset($_from); ?>"><img src="http://traffic.alexa.com/graph?&w=<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 400) : smarty_modifier_default($_tmp, 400)))) ? $this->_run_mod_handler('validate', true, $_tmp, 'int') : smarty_modifier_validate($_tmp, 'int')); ?>
&h=<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['height'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 220) : smarty_modifier_default($_tmp, 220)))) ? $this->_run_mod_handler('validate', true, $_tmp, 'int') : smarty_modifier_validate($_tmp, 'int')); ?>
&o=f&c=1&y=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['type'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 'r') : smarty_modifier_default($_tmp, 'r')); ?>
&b=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['BGCOLOR'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, 'ffffff') : smarty_modifier_default($_tmp, 'ffffff')); ?>
&n=666666&r=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['range'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')))) ? $this->_run_mod_handler('default', true, $_tmp, '3m') : smarty_modifier_default($_tmp, '3m')); ?>
<?php $_from = $this->_tpl_vars['site']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['s']):
?>&u=<?php echo ((is_array($_tmp=$this->_tpl_vars['s'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'urlpathinfo') : smarty_modifier_escape($_tmp, 'urlpathinfo')); ?>
<?php endforeach; endif; unset($_from); ?>"/></a>