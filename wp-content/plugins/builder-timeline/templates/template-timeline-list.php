<?php
/**
 * Timeline List template
 *
 * @var $items
 * @var $args['settings']
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$is_inline_edit_supported=$args['settings']['source_timeline']==='text' && method_exists('Themify_Builder_Component_Base','add_inline_edit_fields');
$animation_effect = self::parse_animation_effect( $args['settings']['animation_effect'], $args['settings'] );
?>
<ul class="tf_rel">
	<?php foreach( $args['settings']['items'] as $item ) : ?>
		<li id="timeline-<?php echo $item['id']; ?>" class="timeline-post <?php echo empty( $item['icon'] ) ? 'without-icon' : 'with-icon'; ?> <?php echo $animation_effect; ?>">
			<span class="module-timeline-date tf_box tf_textr"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('date_timeline',true,false ,'text_source_timeline'); }?>>
				<?php echo $item['date_formatted']; ?>
			</span>
			    <?php if(empty($item['icon'] )) : ?>
				    <div class="module-timeline-dot"></div>
			    <?php else :
					    $icon_styling ='';
						if (( isset($item['icon_color'] ) && '' !== $item['icon_color'] ) || ( isset($item['icon_text_color'] ) && '' !== $item['icon_text_color'] ) ) {
						    if( isset($item['icon_color'] ) && '' !== $item['icon_color'] ){
								$icon_styling= 'background-color:' . Themify_Builder_Stylesheet::get_rgba_color( $item['icon_color'] ). ';';
						    }
						    if(isset($item['icon_text_color'] ) && '' !== $item['icon_text_color']){
								$icon_styling.=  'color:' . Themify_Builder_Stylesheet::get_rgba_color( $item['icon_text_color'] ); 
						    }
							if($icon_styling!==''){
							    $icon_styling = ' style="'.$icon_styling.'"';
							}
						}
					?>
				    <i class="module-timeline-icon"<?php echo $icon_styling; ?>><?php echo themify_get_icon( $item['icon'] ); ?></i>
	
			<?php endif; ?>
			<div class="module-timeline-content-wrap tf_box tf_right">
				<div class="module-timeline-content">
				    <?php if(!empty($item['title'])): ?>
						<h2 class="module-timeline-title"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('title_timeline',!isset( $item['link'] ) ,false,'text_source_timeline'); }?>>    
							<?php if( isset( $item['link'] ) ) : ?>
								<a href="<?php echo $item['link']; ?>"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('title_timeline',true,false,'text_source_timeline'); }?>><?php echo $item['title']; ?></a>
							<?php else : ?>
								<?php echo $item['title']; ?>
							<?php endif; ?>
						</h2>
				    <?php endif; ?>
				    <?php if( ! $item['hide_featured_image'] && !empty($item['image'])): ?>
					    <figure class="module-timeline-image">
							<?php echo themify_get_image(array('src'=>$item['image'],'alt'=>$item['title'],'attr'=>(Themify_Builder::$frontedit_active===true?array('data-name'=>'image_timeline','data-repeat'=>'text_source_timeline'):false)));?>
					    </figure>
				    <?php endif; // hide image ?>
				    <?php if( ! $item['hide_content'] ) : ?>
						<div class="entry-content" itemprop="articleBody"<?php if($is_inline_edit_supported===true){ self::add_inline_edit_fields('content_timeline',true,true ,'text_source_timeline'); }?>>
							<?php echo $item['content']; ?>
						</div><!-- /.entry-content -->
				    <?php endif; //hide_content ?>
				</div><!-- /.timeline-content -->
			</div><!-- /.timeline-content-wrap -->
		</li>
	<?php endforeach; ?>
</ul>