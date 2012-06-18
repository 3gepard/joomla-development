<?php
/**
 * @version		$Id: helper.php 10857 Jun 15, 2010 2:35:38 PM Branimir$
 * @package	Joomla Module Helper - helper.php
 * @copyright	Copyright (C) 2007 - 2010 3Gepard Studio
 * @license		GNU/GPL, see LICENSE.php
 * @author		Branimir  Topic (3Gepard)
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class FlashNewsRotatorHelper {
	/**
	 * @desc Add JQuery and JQuery.cycle script to document header
	 * @param  $params
	 */
	public function addHeaderScripts(&$params) {

		if(!defined('FLASHNEWSROTATOR_SCRIPTS'))
		{
			// add jquery to header
			$document =& JFactory::getDocument();
			$useGoogleAPIs = $params->get('use_google_api_jquery', 0);
			if (!defined('JQUERY')) {
				if ($useGoogleAPIs) {
						$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
				}else{
					$document->addScript('modules'.DS.'mod_flashnewsrotator'.DS.'js'.DS.'jquery-1.4.2.min.js');
				}
				define('JQUERY','JQUERY');
			}
			// add jquery.cycle to header
			if (!defined('JQUERY.CYCLE.JS')) {
				$document->addScript('modules'.DS.'mod_flashnewsrotator'.DS.'js'.DS.'jquery.cycle.js');
				define('JQUERY.CYCLE.JS', 'JQUERY.CYCLE.JS');
			}
			define('FLASHNEWESROTATOR_SCRIPTS', 'FLASHNESROTATOR_SCRIPTS');
		}
	}
	/**
	 * @desc dd style sheet to header
	 * @param $params
	 */
	public function addHeaderStyleSheet() {
		$document =& JFactory::getDocument();
		$document->addStyleSheet('modules'.DS.'mod_flashnewsrotator'.DS.'css'.DS.'flashnewsrotator.css');
	}

	/**
	 * @desc return news items in array list
	 * @param $params
	 */
	public function getLayOut(&$params) {
		$layout = trim($params->get('layout', 'default'));
		return (file_exists(JPATH_ROOT .DS. 'modules' .DS. dirname(__FILE__) .DS. $layout)) ?  $layout : 'default';
	}

	/**
	 * @desc Return news items in array list
	 * @param $params
	 */
	public function getArticles(&$params) {

		global $mainframe;

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$userId		= (int) $user->get('id');

		$count		= (int) $params->get('count', 5);
		$set_article_id	= trim($params->get('set_article_id'));

		$storiesFolder = trim($params->get('images_subdirectory'));

		$show_front	= $params->get('show_front', 1);
		$aid = $user->get('aid', 0);

		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$access = !$contentConfig->get('show_noauth');

		$nullDate = $db->getNullDate();

		$date =& JFactory::getDate();
		$now = $date->toMySQL();

		$where = 'a.state = 1'
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		;

		// Ordering
		switch ($params->get( 'ordering' ))
		{
			case 'm_dsc':
				$ordering = 'a.modified DESC, a.created DESC';
				break;
			case 'c_dsc':
				$ordering = 'a.created DESC';
				break;
			case 'ord':
			default:
				$ordering = 'a.ordering';
				break;
		}

		if ($set_article_id)
		{
			$ids = explode( ',', $set_article_id );
			JArrayHelper::toInteger( $ids );
			$articlesCond = ' AND (a.id IN (' . implode( ',', $ids ) . '))';
		}

		// Content Items only
		$query = 'SELECT a.*, ' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
		($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
			' WHERE '. $where .' AND s.id > 0' .
		($articlesCond ? $articlesCond : '') .
		//			($access ? ' AND a.access <= ' .(int) $aid. ' AND cc.access <= ' .(int) $aid. ' AND s.access <= ' .(int) $aid : '').
		($show_front == '0' ? ' AND f.content_id IS NULL ' : '').
			' AND s.published = 1' .
			' AND cc.published = 1' .
			' ORDER BY '. $ordering;

		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		$lists	= array();
		$i		= 0;
		foreach ( $rows as $row )
		{
			$lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
			$lists[$i]->text = htmlspecialchars( $row->title );
			$lists[$i]->introtext = $row->introtext;
			$lists[$i]->fulltext = $row->fulltext;
			$lists[$i]->params = new JParameter( $row->attribs );
			$i++;
		}

		return $lists;
	}
}