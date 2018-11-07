 
<?php 
global $base_url;
?>
<?php
global $user;

if((arg(0)!='user') && ( !$user->uid )) {
?>
<script type="text/javascript">
if (screen.width >= 768) {
window.location.href = "https://www.roofbag.com/";
} 
</script>	
<?php 
}
/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see bootstrap_preprocess_page()
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see bootstrap_process_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup templates
 */
?><header id="navbar" role="banner" class="<?php print $navbar_classes; ?>">

<div id="mySidenav" class="sidenav">
<?php 
$menu = menu_navigation_links('main-menu');

foreach($menu as $main_menu)
{
if($main_menu['title']=='Home')
{
	
	echo '<a href="'.$base_url.'">'.$main_menu['title'].'</a>'; 
}
else
{
	$link_alias=$main_menu['href'];
	$alias = drupal_get_path_alias($link_alias);
	echo '<a href="'.$base_url.'/'.$alias.'">'.$main_menu['title'].'</a>'; 
}
		
}

?>
 
 
</div>


<script>
function openNav() {
	if(document.getElementById("mySidenav").style.width=="70%")
	{
       document.getElementById("mySidenav").style.width = "0";
	 document.getElementById("fadeMe").style.display = "none";
	}
	else
	{
		document.getElementById("mySidenav").style.width = "70%";
    document.getElementById("fadeMe").style.display = "block";
	}
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
	 document.getElementById("fadeMe").style.display = "none";
}
</script>
<div class="fadeMe" id="fadeMe"></div>
<div class="fadeMe1" id="fadeMe1"></div>
  <div class="<?php print $container_class; ?>">
    <div class="navbar " style="margin-bottom:0px;" data-spy="affix" data-offset-top="197" >
	 <?php if (!empty($primary_nav) || !empty($secondary_nav) || !empty($page['navigation'])): ?>
        <button type="button" class="navbar-toggle " type="button" onclick="openNav()" id="menu1"  style="float:left;" >
          <span class="sr-only"><?php print t('Toggle navigation'); ?></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
		
	<nav role="navigation"  class="dropdown-menu dropdown-menu-left" >
          <?php if (!empty($primary_nav)): ?>
            <?php print render($primary_nav); ?>
          <?php endif; ?>
          <?php if (!empty($secondary_nav)): ?>
            <?php print render($secondary_nav); ?>
          <?php endif; ?>
          <?php if (!empty($page['navigation'])): ?>
            <?php print render($page['navigation']); ?>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
      <?php if ($logo): ?>
             <a class="logo navbar-btn pull-left" href="<?php print $front_page; ?>" title="<?php print t('RoofBag Homepage'); ?>">
     <!-- img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" / -->
          <div id="logo-text">RoofBag</div>
          <div id="tag-line">Travel Made Easy</div>
        </a>
    
		 
		 <div class="navbar-btn pull-left" style="float:right !important;padding-top:5px;">
		 <a href="javascript:void(0)" class=""><i class="fa fa-phone" aria-hidden="true" data-toggle="collapse" data-target=".navbar-collapse" style="margin-right:25px;"></i></a>
         <a href="javascript:void(0)" class=""><i class="fa fa-comments" aria-hidden="true"></i></a>
        </div>
	  <?php endif; ?>
<?php if (!empty($primary_nav) || !empty($secondary_nav) || !empty($page['navigation'])): ?>
      <div class="navbar-collapse collapse" id="navbar-collapse" style="width:100%;">
        <nav role="navigation">
        <ul class="menu nav navbar-nav">
        <li class="first leaf active"><a href="tel:800-276-6322" class="call-from">Call (from USA)</a></li>
        <li class="last leaf"><a href="tel:619-662-0495" class="call-from">Call (from Outside USA)</a></li>
</ul>
        </nav>
      </div>
    <?php endif; ?>
     
    </div>

    
  </div>
</header>

<div class="main-container <?php print $container_class; ?>" style="margin-top: -20px;">

  <header role="banner" id="page-header">
    <?php if (!empty($site_slogan)): ?>
      <p class="lead"><?php print $site_slogan; ?></p>
    <?php endif; ?>

    <?php print render($page['header']); ?>
  </header> <!-- /#page-header -->

  <div class="row">

    <?php if (!empty($page['sidebar_first'])): ?>
      <aside class="col-sm-3" role="complementary">
        <?php print render($page['sidebar_first']); ?>
      </aside>  <!-- /#sidebar-first -->
    <?php endif; ?>
	

    <section<?php print $content_column_class; ?> style="padding-right:0px; padding-left:0px;">
	
      <?php if (!empty($page['highlighted'])): ?>
        <div class="highlighted jumbotron"><?php print render($page['highlighted']); ?></div>
      <?php endif; ?>
      <?php if (!empty($breadcrumb)): print $breadcrumb; endif;?>
     
      <?php print $messages; ?>
      <?php if (!empty($tabs)): ?>
        <?php print render($tabs); ?>
      <?php endif; ?>
      <?php if (!empty($page['help'])): ?>
        <?php print render($page['help']); ?>
      <?php endif; ?>
      <?php if (!empty($action_links)): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
	 <?php print render($page['content_top']); ?>	
	   <h1><?php print $title; ?></h1>
	<?php 
	$query = db_select('cardata', 'u');
	$query->fields('u', array('make')); 
	$query->orderBy('make', 'ASC');
	$results =$query->distinct()->execute();
	echo '
	<select id="make_select" class="form-control" style="display:block;">
	<option>Select make </option>
	';
	while($record = $results->fetchAssoc()) {
		 

	   echo '<option>'.$record['make'].'</option>';
	}
	echo '</select>
	<select id="model_select" class="form-control" style="display:block;">
	<option >Select model</option>
	</select>';
	?>
    </section>

    <?php if (!empty($page['sidebar_second'])): ?>
      <aside class="col-sm-3" role="complementary">
        <?php print render($page['sidebar_second']); ?>
      </aside>  <!-- /#sidebar-second -->
    <?php endif; ?>
	<div  id="info_div">

	</div>
	<div  id="info_div1">

	</div>

	<div class="modal fade" id="myModal11" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title ">Suggested Carrier Size</h4>
		  </div>
		  <div class="modal-body">
			<div id="modal-suggest"></div>
		  </div>
		 
		</div>
	  </div>
	</div>
	<?php 

	 print render($node->body['und']['0']['value']); ?>
	 
	  </div>
	</div>
	<script>

	jQuery('#myModal11').on('hide.bs.modal', function (e) {
	 mysel = document.getElementById('make_select');
		mysel.selectedIndex = 0;
		mysel = document.getElementById('model_select');
		mysel.selectedIndex = 0;
		if (jQuery('#body_style option').length != 0) {
	   mysel = document.getElementById('body_style');
		mysel.selectedIndex = 0;
	}
		
	})
	</script>
	<style>
	/* DivTable.com */
	.divTable{
		display: table;
		width: 100%;
	}
	.divTableRow {
		display: table-row;
	}
	.divTableHeading {
		background-color: #EEE;
		display: table-header-group;
	}
	.divTableCell, .divTableHead {
		border: 1px solid #999999;
		display: table-cell;
		padding: 3px 10px;
	}
	.divTableHeading {
		background-color: #EEE;
		display: table-header-group;
		font-weight: bold;
	}
	.divTableFoot {
		background-color: #EEE;
		display: table-footer-group;
		font-weight: bold;
	}
	.divTableBody {
		display: table-row-group;
	}
	</style>
	<?php if (!empty($page['footer'])): ?>
	  <footer class="footer <?php print $container_class; ?>">
		<?php print render($page['footer']); ?>
	  </footer>
	<?php endif; ?>