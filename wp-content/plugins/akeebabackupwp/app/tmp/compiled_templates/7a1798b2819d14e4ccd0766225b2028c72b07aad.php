<?php /* /home/cicultura/public_html/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/Update/download.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var   \Solo\View\Update\Html  $this */
?>

<div id="downloadProgress">
	<div class="akeeba-block--warning">
		<span><?php echo \Awf\Text\Text::_('COM_AKEEBA_BACKUP_TEXT_BACKINGUP'); ?></span>
	</div>

	<div id="downloadProgressBarContainer" class="akeeba-progress">
		<div id="downloadProgressBar" class="akeeba-progress-fill" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
		</div>
        <div class="akeeba-progress-status" id="downloadProgressBarInfo">0%</div>
    </div>

	<div class="akeeba-block--info" id="downloadProgressInfo">
		<h4>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_DOWNLOAD_LBL_DOWNLOADPROGRESS'); ?>
		</h4>
		<div class="panel-body" id="downloadProgressBarText">

		</div>
	</div>
</div>

<div id="downloadError" style="display: none">
	<div class="akeeba-block--failure">
		<h4>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_DOWNLOAD_ERR_DOWNLOADERROR_HEADER'); ?>
		</h4>
		<div class="panel-body" id="downloadErrorText"></div>
	</div>
</div>
