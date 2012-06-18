<?php
/*
Library:  default.php
Description: Joomla Module Main Library
Date: Jun 15, 2010 11:17:26 AM
Author:  2010  (c) 3Gepard   : (Branimir Topic)
*/

defined('_JEXEC') or die('Restricted access');

// Speed, Effect
$fx			= trim($params->get('fx', 'turnDown'));
$speed		= trim($params->get('speed', '2000'));
$timeout	= trim($params->get('timeout', '3000'));
$pause		= trim($params->get('pause', true));

// Container
$containerResize	= trim($params->get('containerResize',1));
$fit				= trim($params->get('fit', true));

// Navigation
$isBanner		= trim($params->get('isBanner', 0));
$showNavigation = trim($params->get('showNavigation',1));
$showNumbers	= trim($params->get('showNumbers',1));

// id's and classes (html elements)
$rotator		= trim($params->get('rotator', 'rotator'));
$slide			= trim($params->get('slide', 'slide'));
$pager			= trim($params->get('pager', 'pager'));
$readMore		= trim($params->get('readMore',''));
$rotator		= (!$showNavigation || $isBanner) ? $rotator.'-nonav':$rotator;

?>
<div id="<?php echo $rotator;?>">
<?php foreach ($list as $item): ?>
	<div class="<?php echo $slide?>">
		<?php if($item->params->get('show_title', 1) && !$isBanner) :?>
		<h3 class="title"><a href="<?php echo $item->link;?>"><?php echo $item->text?></a></h3>
		<?php endif?>
<?php 
		if ($isBanner) {
			$fulltext = trim($item->fulltext);
			if ($fulltext) $fulltext = "<div class=\"$rotator-fulltext\" style=\"display:block;position:absolute;\">$fulltext</div>";
			echo <<< HTML

			<div style="position:relative;width:100%;height:100%"> 
				$item->introtext
				$fulltext
			</div>

HTML;
		}else{
			echo $item->introtext;
		}
?>
		<?php if(!$isBanner) :?>
		<div class="read-more"><a class="readon" title="<?php echo $item->text;?>" href="<?php echo $item->link?>"><?php echo $readMore?></a></div>
		<?php endif?>
	</div>
<?php endforeach?>
</div>
<?php
if($showNavigation && !$isBanner) :?>
<div id="<?php echo $rotator;?>-navigation">
	<div id="next">&raquo;</div>
	<div id="prev">&laquo;</div>
	<?php if ($showNumbers) :?>
	<div id="<?php echo $pager;?>"></div>
	<?php endif?>
</div>
<?php endif?>
<?php
echo <<< HTML

	<script type="text/javascript">
			$('div#$rotator').cycle({
				fx: '$fx',
				speed: $speed,
				timeout: $timeout,
				pause: $pause,
HTML;

if ($showNavigation) {
echo <<< HTML

				slideExpr: 'div.$slide',
				pager: '#$pager',
				prev: '#prev',
				next: '#next',
HTML;
}
echo <<< HTML

				containerResize: $containerResize,
				fit: $fit
			});
	</script>

HTML;
?>
