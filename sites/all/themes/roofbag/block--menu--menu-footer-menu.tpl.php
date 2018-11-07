<?php
global $base_url;
$menu = menu_navigation_links('menu-footer-menu');
if($menu){
	$menu_out_put ='
	<section id="block-menu-menu-footer-menu" class="block block-menu contextual-links-region clearfix">
	<ul id =""class="menu nav">';
	foreach($menu as $main_menu)
	{
		$menu_out_put .='<li class="leaf">';
		if($main_menu['title']=='About Us')
		{
			$menu_out_put .='<a href="#" class="load_block_content" data-toggle="modal" data-target="#load_block_content" data-block_id="10" data-whatever="'.$main_menu['title'].'">'.$main_menu['title'].'</a>'; 
		}elseif($main_menu['title']=='Shipping')
		{
			$menu_out_put .='<a href="#" class="load_block_content" data-toggle="modal" data-target="#load_block_content" data-block_id="12" data-whatever="'.$main_menu['title'].'">'.$main_menu['title'].'</a>'; 
		}elseif($main_menu['title']=='Contact Us')
		{
			$menu_out_put .='<a href="#" class="load_block_content" data-toggle="modal" data-target="#load_block_content" data-block_id="13" data-whatever="'.$main_menu['title'].'">'.$main_menu['title'].'</a>'; 
		}elseif($main_menu['title']=='Warranty/Returns'){
			$menu_out_put .='<a href="#" class="load_block_content" data-toggle="modal" data-target="#load_block_content" data-block_id="14" data-whatever="'.$main_menu['title'].'">'.$main_menu['title'].'</a>';
		}
		else
		{
			$link_alias=$main_menu['href'];
			$alias = drupal_get_path_alias($link_alias);
			$menu_out_put .='<a href="'.$base_url.'/'.$alias.'">'.$main_menu['title'].'</a>'; 
		}
		$menu_out_put .='</li>';
	}
	$menu_out_put .='</ul>';
	$menu_out_put .='</section>';
}
?>
<div id="mynav" class="<?php print $classes; ?>"<?php print $attributes; ?>>
	<?php
	echo $menu_out_put;
	?>
</div>