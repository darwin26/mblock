<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// set default template
if (!$this->hasConfig('mblock_theme')) {
    $this->setConfig('mblock_theme', 'default_theme');
}
if (!$this->hasConfig('mblock_delete')) {
    $this->setConfig('mblock_delete', 1);
}
if (!$this->hasConfig('mblock_scroll')) {
    $this->setConfig('mblock_scroll', 1);
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());
// delete all assets
rex_dir::deleteFiles($this->getAssetsPath(), true);
// copy assets
rex_dir::copy($this->getPath('assets'), $this->getAssetsPath());