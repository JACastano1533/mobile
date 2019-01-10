<?php
/**
 * @file
 * Default theme implementation to display the basic html structure of a single
 * Drupal page.
 *
 * Variables:
 * - $css: An array of CSS files for the current page.
 * - $language: (object) The language the site is being displayed in.
 *   $language->language contains its textual representation.
 *   $language->dir contains the language direction. It will either be 'ltr' or
 *   'rtl'.
 * - $html_attributes:  String of attributes for the html element. It can be
 *   manipulated through the variable $html_attributes_array from preprocess
 *   functions.
 * - $html_attributes_array: An array of attribute values for the HTML element.
 *   It is flattened into a string within the variable $html_attributes.
 * - $body_attributes:  String of attributes for the BODY element. It can be
 *   manipulated through the variable $body_attributes_array from preprocess
 *   functions.
 * - $body_attributes_array: An array of attribute values for the BODY element.
 *   It is flattened into a string within the variable $body_attributes.
 * - $rdf_namespaces: All the RDF namespace prefixes used in the HTML document.
 * - $grddl_profile: A GRDDL profile allowing agents to extract the RDF data.
 * - $head_title: A modified version of the page title, for use in the TITLE
 *   tag.
 * - $head_title_array: (array) An associative array containing the string parts
 *   that were used to generate the $head_title variable, already prepared to be
 *   output as TITLE tag. The key/value pairs may contain one or more of the
 *   following, depending on conditions:
 *   - title: The title of the current page, if any.
 *   - name: The name of the site.
 *   - slogan: The slogan of the site, if any, and if there is no title.
 * - $head: Markup for the HEAD section (including meta tags, keyword tags, and
 *   so on).
 * - $styles: Style tags necessary to import all CSS files for the page.
 * - $scripts: Script tags necessary to load the JavaScript files and settings
 *   for the page.
 * - $page_top: Initial markup from any modules that have altered the
 *   page. This variable should always be output first, before all other dynamic
 *   content.
 * - $page: The rendered page content.
 * - $page_bottom: Final closing markup from any modules that have altered the
 *   page. This variable should always be output last, after all other dynamic
 *   content.
 * - $classes String of classes that can be used to style contextually through
 *   CSS.
 *
 * @see bootstrap_preprocess_html()
 * @see template_preprocess()
 * @see template_preprocess_html()
 * @see template_process()
 *
 * @ingroup templates
 */

//block office, home and Webplanex IP addresses from: google code, visitor's log
$blockIps = ['67.220.165.162','68.8.201.94','27.54.165.218'];
//$blockIps = ['67.220.165.162','27.54.165.218'];
$clientIps = get_client_ip();
?><!DOCTYPE html>
<html<?php print $html_attributes;?><?php print $rdf_namespaces;?>>

<head>
  <link rel="profile" href="<?php print $grddl_profile; ?>" />
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
  
  <?php print $head; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
  
  <title><?php print $head_title; ?></title>
  <?php print $styles; ?>
  <!-- HTML5 element support for IE6-8 -->
  <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <?php print $scripts; ?>

<?php if( !in_array($clientIps, $blockIps) ){ ?>
<!-- Google Analytics -->
<!-- Javascript tracking snippet -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-317072-3', 'auto');
ga('send', 'pageview');
ga('send','event','link','click','Description');
ga('send','event','link','click','How soon can I get it?');
ga('send','event','link','click','collapseTwoLink');
ga('send','event','link','click','collapseSixLink');
ga('send','event','link','click','Accessories');




</script>
<!-- End Google Analytics -->


  <!-- Google Analytics tracking code -->
  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-317072-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-317072-3');
</script>
<?php } ?>
<!-- js function to get experiment cookie from visitor's browser -->
<!-- This function returns a concatenated string of all test cookies and values in the form of t_1=e_1|t_5=e_2. -->
<!-- If running "hide reviews" test, function returns either of these two strings: t_1=e_1 (customer didn't see the reviews), or t_1=e_2 (customer saw the reviews) -->
<script>
function getTestDataForUser(){
  var returnValue = "";
  var abCookieStart = 'abjs_';
  var abCookieRegExp = new RegExp(abCookieStart+'[^;]*(;|$)','g');
  if (abCookieRegExp.test(document.cookie)) {
    var abCookieMatches = document.cookie.match(abCookieRegExp);
    for(var i = 0; i < abCookieMatches.length; i++){
      var testAndExp = abCookieMatches[i].replace(';','');
      testAndExp = testAndExp.replace(abCookieStart,'');
      returnValue += testAndExp + '|';
    }
  }
  return returnValue;
}
<?php if( !in_array($clientIps, $blockIps) ){ ?>
var dimensionValue = 'NoReviews_vs_Reviews';
ga('set', 'dimension1', dimensionValue);


var testData = getTestDataForUser();
ga('send', 'pageview', {'dimension1': testData});  
<?php } ?>
//console.log(testData);
  
</script>



 
<script>


/**
* Function that tracks a click on an outbound link in Analytics.
* This function takes a valid URL string as an argument, and uses that URL string
* as the event label. Setting the transport method to 'beacon' lets the hit be sent
* using 'navigator.sendBeacon' in browser that support it.
*/
var trackOutboundLink = function(url) {
   ga('send', 'event', 'outbound', 'click', url, {
     'transport': 'beacon',
     'hitCallback': function(){document.location = url;}
   });
}
</script>

  <!--11/17/2017 - heat map code from LuckyOrange.com -->
<script type='text/javascript'>
//window.__lo_site_id = 83234;

//	(function() {
//		var wa = document.createElement('script'); wa.type = 'text/javascript'; wa.async = true;
//		wa.src = 'https://d10lpsik1i8c69.cloudfront.net/w.js';
//		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(wa, s);
//	  })();
</script>
<!-- end of heatmap code from LuckyOrange.com -->

<script type="text/javascript">
<!--
    var LiveHelpSettings = {};
    LiveHelpSettings.server = 'm.roofbag.com';
    LiveHelpSettings.embedded = true;
    (function($) { 
        // JavaScript
        LiveHelpSettings.server = LiveHelpSettings.server.replace(/[a-z][a-z0-9+\-.]*:\/\/|\/livehelp\/*(\/|[a-z0-9\-._~%!$&'()*+,;=:@\/]*(?![a-z0-9\-._~%!$&'()*+,;=:@]))|\/*$/g, '');
        var LiveHelp = document.createElement('script'); LiveHelp.type = 'text/javascript'; LiveHelp.async = true;
        LiveHelp.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + LiveHelpSettings.server + '/livehelp/scripts/jquery.livehelp.min.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(LiveHelp, s);
    })(jQuery);
-->
</script>

  
</head>
<body<?php print $body_attributes; ?>>
  <?php
    // added by webplanex
    if (drupal_is_front_page()) {
      $Userip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
      if( isset($_COOKIE['userTrack']) && $_COOKIE['userTrack'] == $Userip ){
        if( isset($_COOKIE['userTrackDate']) && $_COOKIE['userTrackDate'] == date("Y-m-d") ){

        }else{
          // $group = 0;
          // if( isset($_COOKIE['abjs_t_1']) && $_COOKIE['abjs_t_1'] == 'e_2' ){
          //   $group = 2;
          // }
          // if( isset($_COOKIE['abjs_t_1']) && $_COOKIE['abjs_t_1'] == 'e_1' ){
          //   $group = 1;
          // }
          $group = 0;
          
          $userIpds = str_replace(".", "", $Userip);
          $userIpds = str_split($userIpds);
          $userIpdTotal = array_sum($userIpds);
          $group = $userIpdTotal % 4;
          // $userIpds = explode(".",$Userip);
          // $userIpdTotal = array_sum($userIpds);
          // $userIpdSum = $userIpdTotal / 4;
          // $userIpdMulti = floor($userIpdSum) * 4;
          // $group = $userIpdTotal - $userIpdMulti;

          if( $group <= 0 ){
             $group = 0;
          }
          if( $group >= 3 ){
             $group = 3;
          }

          //$log  = date("m/d/Y H:i:s A")."    ".$_SERVER['HTTP_USER_AGENT']."    ".$Userip."    ".str_replace(".","",$Userip)."    ".$group."    def".PHP_EOL;
          $log  = date("m/d/Y H:i:s A")."\t".$_SERVER['HTTP_USER_AGENT']."\t".$Userip."\t".str_replace(".","",$Userip)."\t".$group."\t Main".PHP_EOL;
          setcookie('userTrack',$Userip);
          setcookie('userTrackDate',date("Y-m-d"));
          file_put_contents('logs/log_'.date("m.d.Y").'.txt', $log, FILE_APPEND);
        }
      }else{
        $group = 0;
        $userIpds = str_replace(".", "", $Userip);
        $userIpds = str_split($userIpds);
        $userIpdTotal = array_sum($userIpds);
        $group = $userIpdTotal % 4;
        // $userIpds = explode(".",$Userip);
        // $userIpdTotal = array_sum($userIpds);
        // $userIpdSum = $userIpdTotal / 4;
        // $userIpdMulti = floor($userIpdSum) * 4;
        // $group = $userIpdTotal - $userIpdMulti;
        if( $group <= 0 ){
           $group = 0;
        }
        if( $group >= 3 ){
           $group = 3;
        }

        //$log  = date("m/d/Y H:i:s A")."    ".substr($_SERVER['HTTP_USER_AGENT'],0,50)."    ".$Userip."    ".str_replace(".","",$Userip)."    ".$group."    def".PHP_EOL;
        $log  = date("m/d/Y H:i:s A")."\t".substr($_SERVER['HTTP_USER_AGENT'],0,50)."\t".$Userip."\t".str_replace(".","",$Userip)."\t".$group."\t Main".PHP_EOL;
        setcookie('userTrack',$Userip);
        setcookie('userTrackDate',date("Y-m-d"));

        //file_put_contents('logs/log_track.txt', $log, FILE_APPEND);

      }
    }
  ?>

  <div id="skip-link">
    <a href="#main-content" class="element-invisible element-focusable"><?php print t('Skip to main content'); ?></a>
  </div>

  <?php print $page_top; ?>
  <?php print $page; ?>
  <?php print $page_bottom; ?>
  
   <!-- Footer Scripts -->
  <script src="https://use.fontawesome.com/d7e0a42690.js"></script>
  <!-- Footer Scripts -->
</body>
</html>
