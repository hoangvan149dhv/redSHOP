<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

?>

<div class="row">
	<div class="col-sm-8">
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title"><?php echo JText::_('COM_REDSHOP_CATEGORY_INFORMATION'); ?></h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<label for="category_name">
						<?php echo $this->form->getLabel('category_name'); ?>
					</label>
					<?php echo $this->form->getInput('category_name'); ?>
				</div>

				<div class="form-group">
					<label for="category_parent_id">
						<?php echo $this->form->getLabel('category_parent_id'); ?>
					</label>
					<?php echo $this->form->getInput('category_parent_id'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('published'); ?>
					</label>
					<?php echo $this->form->getInput('published'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('products_per_page'); ?>
					</label>
					<?php echo $this->form->getInput('products_per_page'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('category_template'); ?>
					</label>
					<?php echo $this->form->getInput('category_template'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('category_more_template'); ?>
					</label>
					<?php echo $this->form->getInput('category_more_template'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('compare_template_id'); ?>
					</label>
					<?php echo $this->form->getInput('compare_template_id'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('category_short_description'); ?>
					</label>
					<?php echo $this->form->getInput('category_short_description'); ?>
				</div>

				<div class="form-group">
					<label>
						<?php echo $this->form->getLabel('category_description'); ?>
					</label>
					<?php echo $this->form->getInput('category_description'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title"><?php echo JText::_('COM_REDSHOP_CATEGORY_IMAGES'); ?></h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<?php
					echo RedshopLayoutHelper::render(
						'component.image',
						array(
							'id'        => 'category_full_image',
							'deleteid'  => 'image_delete',
							'displayid' => 'image_display',
							'type' 	    => 'category',
							'image'     => $this->item->category_full_image
						)
					);
					?>

					<div class="btn-toolbar">
						<?php
						$ilink = JRoute::_('index.php?tmpl=component&option=com_redshop&view=media&layout=thumbs');
						?>
						<a class="modal btn btn-primary inline" title="Image" href="<?php echo $ilink; ?>" rel="{handler: 'iframe', size: {x: 900, y: 500}}">
							<?php echo JText::_('COM_REDSHOP_SELECT_IMAGE'); ?>
						</a>
					</div>

					<?php echo $this->form->getInput('category_image'); ?>
				</div>

			</div>
		</div>

		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title"><?php echo JText::_('COM_REDSHOP_CATEGORY_BACK_IMAGE'); ?></h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<?php
					echo RedshopLayoutHelper::render(
						'component.image',
						array(
							'id'        => 'category_back_full_image',
							'deleteid'  => 'image_back_delete',
							'displayid' => 'image_back',
							'type' 	    => 'category',
							'image'     => $this->item->category_back_full_image
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
</div>

