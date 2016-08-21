<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// rex request
$config = rex_post('config', array(
    array('mblock_theme', 'string'),
    array('submit', 'boolean')
));

// include info page
include rex_path::addon('mblock', 'pages/info.php');

//////////////////////////////////////////////////////////
// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_help_subheadline_1'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');


//////////////////////////////////////////////////////////
// init form
$form = '';

// if submit set config
if ($config['submit']) {
    // show is saved field
    $this->setConfig('mblock_theme', $config['mblock_theme']);
    $form .= rex_view::info(rex_i18n::msg('mblock_config_saved'));
}

// read dir
$themes = MBlockThemeHelper::getThemesInformation();

// open form
$form .= '
  <form action="' . rex_url::currentBackendPage() . '" method="post">
    <fieldset><legend class="middle">' . rex_i18n::msg('mblock_theme_options') . '</legend>
';

// set arrays
$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mblock-config-template">' . rex_i18n::msg('mblock_config_label_template') . '</label>
';

// create select
$select = new rex_select;
$select->setId('rex-mblock-config-template');
$select->setSize(1);
$select->setAttribute('class', 'form-control');
$select->setName('config[mblock_theme]');
// add options
foreach ($themes as $theme) {
    $select->addOption($theme['theme_screen_name'], $theme['theme_path']);
}
$select->setSelected($this->getConfig('mblock_theme'));
$elements['field'] = $select->get();
$formElements[] = $elements;
// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/form.php');

$form .= '</fieldset><fieldset><legend class="middle">' . rex_i18n::msg('mblock_defaults') . '</legend>';

// add checkbox
$formElements = array();
$elements = array();
// $elements['before'] = '<label for="rex-mblock-config-delete">' . rex_i18n::msg('mblock_delete') . '</label>';
$elements['label'] = '<label for="rex-mblock-config-delete">' . rex_i18n::msg('mblock_delete') . '</label>';
$elements['field'] = '<input type="checkbox" id="rex-mblock-config-delete" name="config[mblock_delete]" value="1" ' . ($this->getConfig('mblock_delete') ? ' checked="checked"' : '') . ' />';
$formElements[] = $elements;
// parse checkbox element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/checkbox.php');

// create submit button
$formElements = array();
$elements = array();
$elements['field'] = '
  <input type="submit" class="btn btn-save rex-form-aligned" name="config[submit]" value="' . rex_i18n::msg('mblock_config_save') . '" ' . rex::getAccesskey(rex_i18n::msg('mblock_config_save'), 'save') . ' />
';
$formElements[] = $elements;

// parse submit element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/submit.php');

// close form
$form .= '
    </fieldset>
  </form>
';

//////////////////////////////////////////////////////////
// parse form fragment
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('mblock_config'));
$fragment->setVar('body', $form, false);
echo $fragment->parse('core/page/section.php');