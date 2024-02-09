<?php
/**
 * Comment Count field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field--comment_count.php
 *
 * @var string $type
 * @var array $args
 * @var array $data
 * @var array $meta_data
 * @var array $lang
 * @var boolean $is_single single page
 * @var string $index index in themplate
 *
 * @package Themify PTB
 */
?>

<?php
    $link_to = isset($data['link_to_comment']) && $data['link_to_comment']==='yes';
    $zero = !empty($data['zero'])?PTB_Utils::get_label($data['zero']):'';
    $one = !empty($data['one'])?PTB_Utils::get_label($data['one']):'';
    $more = !empty($data['more'])?PTB_Utils::get_label($data['more']):'';
    if(!$zero){
            $zero = false;
    }
    if(!$one){
            $one = false;
    }
    if(!$more){
            $more = false;
    }
    elseif(strpos('%',$more)===false){
            $more = '% '.$more;
    }
 ?>
<?php if ($link_to): ?>
	<a href="<?php comments_link()?>">
<?php endif; ?>
	<?php comments_number($zero,$one,$more);?>
<?php if ($link_to): ?>
	</a>
<?php endif; ?>
