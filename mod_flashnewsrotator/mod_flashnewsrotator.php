<?php
/**
* @version		$Id: mod_latestnews.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

define('FLASHNEWSROTATOR_IMG_BASE', JPATH_ROOT .DS. 'images');
define('FLASHNEWSROTATOR_IMG_STORIES', FLASHNEWSROTATOR_IMG_BASE .DS.'stories');
define('FLASHNEWSROTATOR_IMG_BASEURL', JURI::root() .DS. 'images');
define('FLASHNEWSROTATOR_DUMMY_IMG', dirname(__FILE__) .DS. 'images' .DS. 'dummy-image.jpg');
define('FLASHNEWSROTATOR_IMG_FILES', 'xcf|odg|gif|jpg|png|bmp');

$list = FlashNewsRotatorHelper::getArticles($params);

FlashNewsRotatorHelper::addHeaderScripts($params);
FlashNewsRotatorHelper::addHeaderStyleSheet();

$layout = FlashNewsRotatorHelper::getLayOut($params);
require(JModuleHelper::getLayoutPath('mod_flashnewsrotator', $layout));