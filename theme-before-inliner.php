<?php
/*
 * Name: SRD Events Newsletter
 */

global $newsletter; // Newsletter object
global $post; // Current post managed by WordPress

if (!defined('ABSPATH'))
    exit;

/*
 * Some variabled are prepared by Newsletter Plus and are available inside the theme,
 * for example the theme options used to build the email body as configured by blog
 * owner.
 *
 * $theme_options - is an associative array with theme options: every option starts
 * with "theme_" as required. See the theme-options.php file for details.
 * Inside that array there are the autmated email options as well, if needed.
 * A special value can be present in theme_options and is the "last_run" which indicates
 * when th automated email has been composed last time. Is should be used to find if
 * there are now posts or not.
 *
 * $is_test - if true it means we are composing an email for test purpose.
 */

/* Upcoming events */
$filtersUpcomingWeek = array();
$filtersUpcomingWeek['posts_per_page'] = (int) $theme_options['theme_max_events_by_dates'];
if ($filtersUpcomingWeek['posts_per_page'] == 0) {
    $filtersUpcomingWeek['posts_per_page'] = 10;
}

global $ai1ec_registry;
$date_system = $ai1ec_registry->get( 'date.system' );
$search = $ai1ec_registry->get('model.search');

if (isset($theme_options['theme_start_date']) && !empty($theme_options['theme_start_date'])) {
  $dtStartDate = date_create_from_format( 'j.n.Y', $theme_options['theme_start_date'] );
  date_time_set($dtStartDate, 24, 0, 0);
  $iStartTimestamp = date_timestamp_get($dtStartDate);
} else {
  $iStartTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + 1);
  $theme_options['theme_start_date'] = date( 'j.n.Y', $iStartTimestamp );
}
if (isset($theme_options['theme_end_date']) && !empty($theme_options['theme_end_date'])) {
  $dtEndDate = date_create_from_format( 'j.n.Y', $theme_options['theme_end_date'] );
  date_time_set($dtEndDate, 24, 0, 0);
  $iEndTimestamp = date_timestamp_get($dtEndDate);
} else {
  $iEndTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + 1 + 7) - 1;
  $theme_options['theme_end_date'] = date( 'j.n.Y', $iEndTimestamp );
}
$theme_subject = "Newsletter " . $theme_options['theme_start_date'] . " - " . $theme_options['theme_end_date'];

// gets time
$start_time = $ai1ec_registry->get( 'date.time', $iStartTimestamp, 'sys.default' );
$end_time = $ai1ec_registry->get( 'date.time', $iEndTimestamp, 'sys.default' );
  
$aEventsUpcomingWeek = $search->get_events_between($start_time, $end_time, array(), true);
/* Upcoming events - end */

/* Latest events */
$filtersLatest = array();
$filtersLatest['posts_per_page'] = (int) $theme_options['theme_max_events_latest'];
if ($filtersLatest['posts_per_page'] == 0) {
    $filtersLatest['posts_per_page'] = 10;
}
$filtersLatest['post_type'] = array('ai1ec_event');
$aEventsLatest = get_posts($filtersLatest);
/* Latest events - end */

// Styles
$color = $theme_options['theme_color'];
if (empty($color))
    $color = '#777';

$GLOBALS['disable_ai1ec_excerpt_filter'] = true;

$font = $theme_options['theme_font'];
$font_size = $theme_options['theme_font_size'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="format-detection" content="telephone=no"/>
  <title><?php echo $theme_subject; ?></title>
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700,300&amp;subset=latin,cyrillic,greek" rel="stylesheet" type="text/css" />
  <style type="text/css">

    /* Resets: see reset.css for details */
    .ReadMsgBody { width: 100%; background-color: #ffffff;}
    .ExternalClass {width: 100%; background-color: #ffffff;}
    .ExternalClass, .ExternalClass p, .ExternalClass span,
    .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
    #outlook a{ padding:0;}
    body{width: 100%; height: 100%; background-color: #ffffff; margin:0; padding:0;}
    body{ -webkit-text-size-adjust:none; -ms-text-size-adjust:none; }
    html{width:100%;}
    table {mso-table-lspace:0pt; mso-table-rspace:0pt; border-spacing:0;}
    table td {border-collapse:collapse;}
    table p{margin:0;}
    br, strong br, b br, em br, i br { line-height:100%; }
    div, p, a, li, td { -webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
    h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
    span a { text-decoration: none !important;}
    a{ text-decoration: none !important; }
    img{height: auto !important; line-height: 100%; outline: none; text-decoration: none;  -ms-interpolation-mode:bicubic;}
    .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited,
    .yshortcuts a:hover, .yshortcuts a span { text-decoration: none !important; border-bottom: none !important;}
    /*mailChimp class*/
    .default-edit-image{
    height:20px;
    }
    ul{padding-left:10px; margin:0;}
    .tpl-repeatblock {
    padding: 0px !important;
    border: 1px dotted rgba(0,0,0,0.2);
    }
    .no-word-break{
    word-break:normal !important;
    }
    @media only screen and (max-width:800px){
    table[style*="max-width:800px"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
    table[style*="max-width:800px"] img{width:100% !important; height:auto !important; max-width:100% !important;}
    }
    @media only screen and (max-width: 640px){
    /* mobile setting */
    table[class="container"]{width:100%!important; max-width:100%!important; min-width:100%!important;
    padding-left:20px!important; padding-right:20px!important; text-align: center!important; clear: both;}
    td[class="container"]{width:100%!important; padding-left:20px!important; padding-right:20px!important; clear: both;}
    table[class="full-width"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
    table[class="full-width-center"] {width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    table[class="force-240-center"]{width:240px !important; clear: both; margin:0 auto; float:none;}
    table[class="auto-center"] {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"]{width: auto!important; max-width:75%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    table[class="col-3"],table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important;}
    table[class="col-2"]{width:47.3%!important; max-width:100%!important;}
    *[class="full-block"]{width:100% !important; display:inline-block !important; clear: both; }
    /* image */
    td[class="image-full-width"]{width:100% !important; height:auto !important; max-width:100% !important;}
    td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important;}
    /* helper */
    table[class="space-w-20"]{width:3.57%!important; max-width:20px!important; min-width:3.5% !important;}
    table[class="space-w-20"] td:first-child{width:3.5%!important; max-width:20px!important; min-width:3.5% !important;}
    table[class="space-w-25"]{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
    table[class="space-w-25"] td:first-child{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
    table[class="space-w-30"]{width:5.35%!important; max-width:30px!important; min-width:5.35% !important;}
    table[class="space-w-30"] td:first-child{width:5.35%!important; max-width:30px!important; min-width:5.35% !important;}
    table[class="fix-w-20"]{width:20px!important; max-width:20px!important; min-width:20px!important;}
    table[class="fix-w-20"] td:first-child{width:20px!important; max-width:20px!important; min-width:20px !important;}
    *[class="h-10"]{display:block !important;  height:10px !important;}
    *[class="h-20"]{display:block !important;  height:20px !important;}
    *[class="h-30"]{display:block !important; height:30px !important;}
    *[class="h-40"]{display:block !important;  height:40px !important;}
    *[class="remove-640"]{display:none !important;}
    *[class="text-left"]{text-align:left !important;}
    *[class="clear-pad"]{padding:0 !important;}
    }
    @media only screen and (max-width: 479px){
    /* mobile setting */
    table[class="container"]{width:100%!important; max-width:100%!important; min-width:124px!important;
    padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
    td[class="container"]{width:100%!important; padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
    table[class="full-width"],table[class="full-width-479"]{width:100%!important; max-width:100%!important; min-width:124px!important; clear: both;}
    table[class="full-width-center"] {width: 100%!important; max-width:100%!important; min-width:124px!important; text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"]{width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    table[class="col-3"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
    table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important; }
    table[class="col-2"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
    table[class="col-2-not-full"]{width:30.35%!important; max-width:100%!important; }
    *[class="full-block-479"]{display:inline-block !important; width:100% !important; clear: both; }
    /* image */
    td[class="image-full-width"] {width:100% !important; height:auto !important; max-width:100% !important; min-width:124px !important;}
    td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:124px !important;}
    td[class="image-min-80"] {width:100% !important; height:auto !important; max-width:100% !important; min-width:80px !important;}
    td[class="image-min-80"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:80px !important;}
    td[class="image-min-100"] {width:100% !important; height:auto !important; max-width:100% !important; min-width:100px !important;}
    td[class="image-min-100"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:100px !important;}
    /* halper */
    table[class="space-w-20"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-20"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-25"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-25"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-30"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-30"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
    *[class="remove-479"]{display:none !important;}
    table[width="595"]{
    width:100% !important;
    }
    }
    td ul{list-style: initial; margin:0; padding-left:20px;}

    @media only screen and (max-width: 640px){ .image-100-percent{ width:100%!important; height: auto !important; max-width: 100% !important; min-width: 124px !important;}}body{background-color:#efefef;} .default-edit-image{height:20px;} tr.tpl-repeatblock , tr.tpl-repeatblock > td{ display:block !important;} .tpl-repeatblock {padding: 0px !important;border: 1px dotted rgba(0,0,0,0.2);} table[width="595"]{width:100% !important;}a img{ border: 0 !important;}
    a:active{color:initial } a:visited{color:initial }
    .tpl-content{padding:0 !important;}
    .full-mb,*[fix="full-mb"]{width:100%!important;} .auto-mb,*[fix="auto-mb"]{width:auto!important;}
    
    body{font-size:12px; width:100%; height:100%; font-family: 'Open Sans', Arial, Helvetica, sans-serif;}
    #mainStructure{background-color: #efefef; width: 800px; max-width: 800px; outline: rgb(239, 239, 239) solid 1px; box-shadow: rgb(224, 224, 224) 0px 0px 5px; margin: 0px auto;}
    #td_8cbd_0{background-color: #eef0f3;}
    #table_8cbd_0{background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_1{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_1{height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_2{font-size: 14px; color: #888888; font-weight: 300; text-align: right; word-break: break-word; line-height: 22px;}
    #div_8cbd_0{text-align: right; font-size: 14px;}
    #span_8cbd_0{line-height: 20px; font-size: 12px;}
    #a_8cbd_0{color: #6689ac; border-style: none; text-decoration: none !important;}
    #td_8cbd_3{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_2{margin: 0px auto;}
    #td_8cbd_4{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_5{background-color: #eef0f3;}
    #table_8cbd_3{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_4{margin: 0px auto;}
    #td_8cbd_6{width: 600px;}
    #img_8cbd_0{max-width: 600px; display: block !important; width: 600px; height: auto;}
    #td_8cbd_7{background-color: #eef0f3;}
    #table_8cbd_5{min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;}
    #table_8cbd_6{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_8{width: 560px;}
    #img_8cbd_1{max-width:560px; display:block!important;}
    #td_8cbd_9{height: 8px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_10{background-color: #eef0f3;}
    #table_8cbd_7{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_8{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_11{height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_12{font-size: 14px; color: #888888; font-weight: 300; text-align: center; word-break: break-word; line-height: 22px;}
    #span_8cbd_1{text-decoration: none;}
    #td_8cbd_13{height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_9{margin: 0px auto;}
    #td_8cbd_14{padding-left:10px; padding-right:10px; padding-top:10px; padding-bottom:10px;}
    #table_8cbd_10{background-color: #6dabdb; border-radius: 3px; margin: 0px auto;}
    #td_8cbd_15{font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; background-clip: padding-box; padding-left: 32px; padding-right: 32px; word-break: break-word; line-height: 22px;}
    #a_8cbd_1{border-style: none; text-decoration: none !important; font-weight: 400; color: #ffffff;}
    #td_8cbd_16{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_17{background-color: #eef0f3;}
    #table_8cbd_11{background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_12{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_18{height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_19{font-size: 14px; color: #888888; font-weight: 300; text-align: right; word-break: break-word; line-height: 22px;}
    #td_8cbd_20{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_13{margin: 0px auto;}
    #td_8cbd_21{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_22{background-color: #eef0f3;}
    #table_8cbd_14{background-color: #ff7e00; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_15{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_23{height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_24{font-size: 14px; color: #888888; font-weight: 300; text-align: left; word-break: break-word; line-height: 22px;}
    #span_8cbd_2{text-decoration: none; color: #ffffff; line-height: 44px; font-size: 36px;}
    #td_8cbd_25{height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_16{margin: 0px auto;}
    #td_8cbd_26{height: 4px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_27{background-color: #eef0f3;}
    #table_8cbd_17{min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;}
    #table_8cbd_18{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_28{width: 560px;}
    #img_8cbd_2{max-width: 560px; display: block !important;}
    #td_8cbd_29{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_30{background-color: #eef0f3;}
    #table_8cbd_19{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_20{margin: 0px auto;}
    #td_8cbd_31{width: 600px;}
    #img_8cbd_3{max-width: 600px; display: block !important; width: 600px; height: auto;}
    #td_8cbd_32{background-color: #eef0f3;}
    #table_8cbd_21{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #td_8cbd_33{background-color: #eef0f3;}
    #table_8cbd_22{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_23{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_34{height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_35{font-size: 14px; color: #888888; font-weight: 300; text-align: left; word-break: break-word; line-height: 22px;}
    #div_8cbd_1{text-align: left; font-size: 14px; font-weight: 300;}
    #span_8cbd_3{text-decoration: none;}
    #span_8cbd_4{color: #ff6600; line-height: 44px; font-size: 36px;}
    #span_8cbd_5{color: #808080;}
    #td_8cbd_36{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_24{margin: 0px auto;}
    #td_8cbd_37{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_38{background-color: #eef0f3;}
    #table_8cbd_25{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_26{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_39{height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_27{margin: 0px auto;}
    #table_8cbd_28{margin:0 auto; mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_40{padding-left:5px; padding-right:5px; padding-top:10px; padding-bottom:10px;}
    #table_8cbd_29{background-color: #6dabdb; border-radius: 3px; margin: 0px auto;}
    #td_8cbd_41{width: 18px;}
    #td_8cbd_42{padding-right: 10px; width: 14px;}
    #img_8cbd_4{max-width: 14px; display: block !important;}
    #td_8cbd_43{font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; background-clip: padding-box; padding-right: 18px; word-break: break-word; line-height: 22px;}
    #a_8cbd_2{border-style: none; text-decoration: none !important; font-weight: 400; color: #ffffff;}
    #table_8cbd_30{min-width: 1px; height: 1px; border-spacing: 0px; width: 1px;mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_44{display: block; width: 1px; height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_31{margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #table_8cbd_32{border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;}
    #td_8cbd_45{font-size: 14px; color: #888888; font-weight: 600; background-clip: padding-box; text-align: right; word-break: break-word; line-height: 22px;}
    #table_8cbd_33{border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;}
    #td_8cbd_46{font-size: 14px; color: #888888; font-weight: 600; background-clip: padding-box; text-align: left; word-break: break-word; line-height: 22px;}
    #td_8cbd_47{height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_48{background-color: #eef0f3;}
    #table_8cbd_34{min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;}
    #table_8cbd_35{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_49{width: 560px;}
    #img_8cbd_5{max-width:560px; display:block!important;}
    #td_8cbd_50{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_51{background-color: #eef0f3;}
    #table_8cbd_36{background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_37{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_52{height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_53{font-size: 14px; color: #888888; font-weight: 300; text-align: right; word-break: break-word; line-height: 22px;}
    #td_8cbd_54{height: 27px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_38{margin: 0px auto;}
    #td_8cbd_55{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_56{background-color: #eef0f3;}
    #table_8cbd_39{background-color: #ff7e00; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_40{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_57{height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_58{font-size: 14px; color: #888888; font-weight: 300; text-align: left; word-break: break-word; line-height: 22px;}
    #span_8cbd_6{text-decoration: none; color: #ffffff; line-height: 44px; font-size: 36px;}
    #td_8cbd_59{height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_41{margin: 0px auto;}
    #td_8cbd_60{height: 4px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_61{background-color: #eef0f3;}
    #table_8cbd_42{min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;}
    #table_8cbd_43{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_62{width: 560px;}
    #img_8cbd_6{max-width: 560px; display: block !important;}
    #td_8cbd_63{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_64{background-color: #eef0f3;}
    #table_8cbd_44{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_45{margin: 0px auto;}
    #td_8cbd_65{width: 600px;}
    #img_8cbd_7{max-width: 600px; display: block !important; width: 600px; height: auto;}
    #td_8cbd_66{background-color: #eef0f3;}
    #table_8cbd_46{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #td_8cbd_67{background-color: #eef0f3;}
    #table_8cbd_47{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_48{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_68{height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_69{font-size: 14px; color: #888888; font-weight: 300; text-align: left; word-break: break-word; line-height: 22px;}
    #span_8cbd_7{text-decoration: none;}
    #span_8cbd_8{color: #ff6600; line-height: 44px; font-size: 36px;}
    #span_8cbd_9{color: #808080;}
    #td_8cbd_70{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_49{margin: 0px auto;}
    #td_8cbd_71{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_72{background-color: #eef0f3;}
    #table_8cbd_50{background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_51{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_73{height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_52{margin: 0px auto;}
    #table_8cbd_53{margin:0 auto; mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_74{padding-left:5px; padding-right:5px; padding-top:10px; padding-bottom:10px;}
    #table_8cbd_54{background-color: #6dabdb; border-radius: 3px; margin: 0px auto;}
    #td_8cbd_75{width: 18px;}
    #td_8cbd_76{padding-right: 10px; width: 14px;}
    #img_8cbd_8{max-width: 14px; display: block !important;}
    #td_8cbd_77{font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; background-clip: padding-box; padding-right: 18px; word-break: break-word; line-height: 22px;}
    #a_8cbd_3{border-style: none; text-decoration: none !important; font-weight: 400; color: #ffffff;}
    #table_8cbd_55{min-width: 1px; height: 1px; border-spacing: 0px; width: 1px;mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_78{display: block; width: 1px; height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_56{margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #table_8cbd_57{border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;}
    #td_8cbd_79{font-size: 14px; color: #888888; font-weight: 600; background-clip: padding-box; text-align: right; word-break: break-word; line-height: 22px;}
    #table_8cbd_58{border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;}
    #td_8cbd_80{font-size: 14px; color: #888888; font-weight: 600; background-clip: padding-box; text-align: left; word-break: break-word; line-height: 22px;}
    #td_8cbd_81{height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_82{background-color: #eef0f3;}
    #table_8cbd_59{min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;}
    #table_8cbd_60{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_83{width: 560px;}
    #img_8cbd_9{max-width:560px; display:block!important;}
    #td_8cbd_84{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_85{background-color: #eef0f3;}
    #table_8cbd_61{background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_62{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_86{height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_87{font-size: 14px; color: #888888; font-weight: 300; text-align: right; word-break: break-word; line-height: 22px;}
    #td_8cbd_88{height: 27px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_63{margin: 0px auto;}
    #td_8cbd_89{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_90{background-color: #323537;}
    #table_8cbd_64{min-width: 600px; background-color: #323537; width: 600px; margin: 0px auto;}
    #table_8cbd_65{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_91{height: 21px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_66{mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_92{font-size: 14px; color: #888888; font-weight: normal; text-align: left; word-break: break-word; line-height: 22px;}
    #div_8cbd_2{font-weight: 400; text-decoration: none; color: #ffffff;}
    #a_8cbd_4{border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; color: #ffffff;}
    #table_8cbd_67{height: 1px; border-spacing: 0px; width: 20px;mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_93{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_68{mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #table_8cbd_69{margin: 0px auto;}
    #td_8cbd_94{padding-left: 3px; padding-right: 3px; width: 25px;}
    #a_8cbd_5{text-decoration: none !important; font-size: inherit; border-style: none;}
    #img_8cbd_10{max-width: 25px; height: auto; display: block !important;}
    #td_8cbd_95{height: 18px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #div_8cbd_3{text-align: left; font-size: 13px; font-weight: 400; text-decoration: none; line-height: 21px; color: #ffffff;}
    #td_8cbd_96{height: 18px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #td_8cbd_97{background-color: #121212;}
    #table_8cbd_70{background-color: #121212; min-width: 600px; width: 600px; margin: 0px auto;}
    #table_8cbd_71{table-layout: fixed; width: 560px; margin: 0px auto;}
    #td_8cbd_98{height: 6px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_72{mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_99{font-size: 14px; color: #ffffff; font-weight: normal; text-align: left; word-break: break-word; line-height: 22px;}
    #a_8cbd_6{color: #999999; text-decoration: none !important; border-style: none;}
    #table_8cbd_73{height: 1px; border-spacing: 0px; width: 20px;mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_100{height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;}
    #table_8cbd_74{mso-table-lspace:0pt; mso-table-rspace:0pt;}
    #td_8cbd_101{color: #999999; font-weight: normal; text-align: right; word-break: break-word; line-height: 20px; font-size: 12px;}
    #td_8cbd_102{height: 19px; font-size: 0px; line-height: 0; border-collapse: collapse;}
  </style>
  <!--[if gte mso 15]>
    <style type="text/css">
      a{text-decoration: none !important;}
      body { font-size: 0; line-height: 0; }
      tr { font-size:1px; mso-line-height-alt:0; mso-margin-top-alt:1px; }
      table { font-size:1px; line-height:0; mso-margin-top-alt:1px; }
      body,table,td,span,a,font{font-family: Arial, Helvetica, sans-serif !important;}
      a img{ border: 0 !important;}
    </style>
  <![endif]-->
  <!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
  </head>
  <body>
    <table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" >
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_0" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_0">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_1">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_1">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_2">
                            <div id="div_8cbd_0">
                                <span id="span_8cbd_0">
                                    Ak sa vám Newsleter nezobrazuje správne, 
                                    <a href="{email_url}" id="a_8cbd_0">
                                      kliknite sem.
                                    </a>
                                </span>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_3">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_2">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_4">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_5" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" id="table_8cbd_3">
              <tbody>
                <tr>
                  <td valign="top">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_4">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" id="td_8cbd_6">
                            <img src="<?php echo $theme_url; ?>/images/20170126185928_news_top_1.jpg" width="600" id="img_8cbd_0" alt="image" border="0" hspace="0" vspace="0" height="auto"/>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_7" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_5">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_6">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_8">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" id="img_8cbd_1" border="0" hspace="0" vspace="0"/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="8" id="td_8cbd_9">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_10" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_7">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_8">
                      <tbody>
                        <tr>
                          <td valign="top" height="9" id="td_8cbd_11">
                          </td>
                        </tr>
                        <tr>
                          <td align="center" id="td_8cbd_12">
                            <span id="span_8cbd_1">
                                Prinášame vám aktuálne udalosti zo sveta salsy, bachaty a kizomby na nadchádzajúci týždeň a niektoré najnovšie pridané akcie.
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="2" id="td_8cbd_13">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_9">
                              <tbody>
                                <tr>
                                  <td valign="top" id="td_8cbd_14" class="full-block">
                                    <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_10">
                                      <tbody>
                                        <tr>

                                          <td width="auto" align="center" valign="middle" height="40" id="td_8cbd_15">
                                            <a href="{blog_url}" id="a_8cbd_1">
                                              Navštíviť stránku
                                            </a>
                                            <br/>
                                          </td>

                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_16">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_17" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_11">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_12">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_18">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_19">
                            <br/>
                              </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_20">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_13">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_21">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>


<?php
if (count($aEventsUpcomingWeek) > 0) :
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_22" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_14">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_15">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_23">
                          </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_24">
                            <div>
                              <span id="span_8cbd_2">
                                  Udalosti na tento týždeň
                              </span>
                              <br/>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="2" id="td_8cbd_25">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_16">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="4" id="td_8cbd_26">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_27" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_17">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_18">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_28">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" id="img_8cbd_2" border="0" hspace="0" vspace="0"/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_29">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>

<?php
  $iCnt = 1;
  foreach ($aEventsUpcomingWeek as $oEvent) :
    if ($iCnt > $filtersUpcomingWeek['posts_per_page']) {
      break;
    }
    $tsStart = $oEvent->get( 'start' );
    $strStart = $ai1ec_registry->get('view.event.time')->get_long_date($tsStart);
    $tsEnd = $oEvent->get( 'end' );
    $strEnd = $ai1ec_registry->get('view.event.time')->get_long_date($tsEnd);
    $post = $oEvent->get( 'post' );
    setup_postdata($post);
//     $image = nt_post_image(get_the_ID(), 'thumbnail');
//     $image = get_the_post_thumbnail( null );
    $aImage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' );
    $strVenue = ai1ecf_fix_location($oEvent->get( 'venue' ), $post->ID, $oEvent->get("address"), $oEvent->get("contact_name"), true);
    $image = $aImage[0];
    
    $strExcerpt = get_the_excerpt();
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($strExcerpt, 'HTML-ENTITIES', 'UTF-8'));
    $selector = new DOMXPath($dom);
    foreach($selector->query('//div[contains(attribute::class, "ai1ec-excerpt")]') as $e ) {
      $e->parentNode->removeChild($e);
    }
    $strExcerpt = $dom->saveHTML($dom->documentElement);
    $strExcerpt = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $strExcerpt);
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_30" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" id="table_8cbd_19">
              <tbody>
                <tr>
                  <td valign="top">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_20">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" id="td_8cbd_31">
                            <a target="_blank"  href="<?php echo get_permalink($post); ?>">
                              <img src="<?php echo $image; ?>" width="600" id="img_8cbd_3" alt="image" border="0" hspace="0" vspace="0" height="auto"/>
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_32" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_21">
              <tbody>
                <tr>
                  <td>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_33" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_22">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_23">
                      <tbody>
                        <tr>
                          <td valign="top" height="20" id="td_8cbd_34">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_35">
                            <div id="div_8cbd_1">
                                <span id="span_8cbd_3">
                                    <span id="span_8cbd_4">
                                        <?php the_title(); ?>
                                    </span>
                                    <br/>
                                    <span id="span_8cbd_5">
                                        <?php echo $strExcerpt; ?>
                                    </span>
                                    <br/>
                                </span>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_36">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_24">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_37">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_38" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_25">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_26">
                      <tbody>
                        <tr>
                          <td valign="top" height="9" id="td_8cbd_39">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" align="center">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_27">
                              <tbody>
                                <tr>
                                  <td valign="middle" class="full-block">
                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" id="table_8cbd_28">
                                      <tbody>
                                        <tr>
                                          <td class="clear-pad" valign="top" id="td_8cbd_40">
                                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_29">
                                              <tbody>
                                                <tr>
                                                  <td valign="middle" width="18" id="td_8cbd_41">
                                                  </td>
                                                  <td valign="middle" id="td_8cbd_42" width="14">
                                                    <img src="<?php echo $theme_url; ?>/images/icon-arrow.png" width="14" id="img_8cbd_4" alt="icon-arrow" border="0" hspace="0" vspace="0"/>
                                                  </td>
                                                  <td width="auto" align="center" valign="middle" height="40" id="td_8cbd_43">
                                                    <a href="<?php echo get_permalink($post); ?>" id="a_8cbd_2">
                                                            Viac info
                                                    </a>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <table width="1" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_30">
                                      <tbody>
                                        <tr>
                                          <td height="1" width="1" class="h-20" id="td_8cbd_44">
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_31" class="full-width">
                                      <tbody>
                                        <tr>
                                          <td valign="middle">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_32">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="right" valign="top" id="td_8cbd_45">
                                                    <div>
                                                      ↗ &nbsp;<?php echo $strStart; ?>
                                                      <br/>
                                                      ↘ &nbsp;<?php echo $strEnd; ?>
                                                    </div>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                          <td valign="middle">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_33">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="left" valign="top" id="td_8cbd_46">
                                                    <div>
                                                      <?php echo $strVenue; ?>
                                                    </div>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="20" id="td_8cbd_47">
                            &nbsp;
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_48" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_34">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_35">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_49">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space.png" width="560" alt="shadow-space" id="img_8cbd_5" border="0" hspace="0" vspace="0"/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_50">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
<?php
    $iCnt++;
  endforeach;
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_51" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_36">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_37">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_52">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_53">
                            <br/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="27" id="td_8cbd_54">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_38">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_55">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
<?php
endif;

if (count($aEventsLatest) > 0) :
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_56" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_39">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_40">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_57">
                          </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_58">
                            <div>
                              <span id="span_8cbd_6">
                                Nové udalosti
                              </span>
                              <br/>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="2" id="td_8cbd_59">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_41">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="4" id="td_8cbd_60">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_61" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_42">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_43">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_62">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" id="img_8cbd_6" border="0" hspace="0" vspace="0"/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_63">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>

<?php
  $iCnt = 1;
  foreach ($aEventsLatest as $post) :
    $oEvent = ai1ecf_get_event_by_post_id( $post->ID );
    $tsStart = $oEvent->get( 'start' );
    $strStart = $ai1ec_registry->get('view.event.time')->get_long_date($tsStart);
    $tsEnd = $oEvent->get( 'end' );
    $strEnd = $ai1ec_registry->get('view.event.time')->get_long_date($tsEnd);
    setup_postdata($post);

    $aImage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' );
    $strVenue = ai1ecf_fix_location($oEvent->get( 'venue' ), $post->ID, $oEvent->get("address"), $oEvent->get("contact_name"), true);
    $image = $aImage[0];
    
    $strExcerpt = get_the_excerpt();
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($strExcerpt, 'HTML-ENTITIES', 'UTF-8'));
    $selector = new DOMXPath($dom);
    foreach($selector->query('//div[contains(attribute::class, "ai1ec-excerpt")]') as $e ) {
      $e->parentNode->removeChild($e);
    }
    $strExcerpt = $dom->saveHTML($dom->documentElement);
    $strExcerpt = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $strExcerpt);
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_64" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" id="table_8cbd_44">
              <tbody>
                <tr>
                  <td valign="top">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_45">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" id="td_8cbd_65">
                            <a target="_blank"  href="<?php echo get_permalink($post); ?>">
                              <img src="<?php echo $image; ?>" width="600" id="img_8cbd_7" alt="image" border="0" hspace="0" vspace="0" height="auto"/>
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_66" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_46">
              <tbody>
                <tr>
                  <td>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_67" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_47">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_48">
                      <tbody>
                        <tr>
                          <td valign="top" height="20" id="td_8cbd_68">
                            &nbsp;
                        </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_69">
                            <div>
                                <span id="span_8cbd_7">
                                    <span id="span_8cbd_8">
                                        <?php the_title(); ?>
                                    </span>
                                    <br/>
                                    <span id="span_8cbd_9">
                                        <?php echo $strExcerpt; ?>
                                    </span>
                                    <br/>
                                </span>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_70">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_49">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_71">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_72" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_50">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_51">
                      <tbody>
                        <tr>
                          <td valign="top" height="9" id="td_8cbd_73">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" align="center">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_52">
                              <tbody>
                                <tr>
                                  <td valign="middle" class="full-block">
                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" id="table_8cbd_53">
                                      <tbody>
                                        <tr>
                                          <td class="clear-pad" valign="top" id="td_8cbd_74">
                                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_54">
                                              <tbody>
                                                <tr>
                                                  <td valign="middle" width="18" id="td_8cbd_75">
                                                  </td>
                                                  <td valign="middle" id="td_8cbd_76" width="14">
                                                    <img src="<?php echo $theme_url; ?>/images/icon-arrow.png" width="14" id="img_8cbd_8" alt="icon-arrow" border="0" hspace="0" vspace="0"/>
                                                  </td>
                                                  <td width="auto" align="center" valign="middle" height="40" id="td_8cbd_77">
                                                    <a href="<?php echo get_permalink($post); ?>" id="a_8cbd_3">
                                                      Viac info
                                                    </a>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <table width="1" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_55">
                                      <tbody>
                                        <tr>
                                          <td height="1" width="1" class="h-20" id="td_8cbd_78">
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_56" class="full-width">
                                      <tbody>
                                        <tr>
                                          <td valign="middle">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_57">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="right" valign="top" id="td_8cbd_79">
                                                    <div>
                                                      ↗ &nbsp;<?php echo $strStart; ?>
                                                      <br/>
                                                      ↘ &nbsp;<?php echo $strEnd; ?>
                                                    </div>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                          <td valign="middle">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_58">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="left" valign="top" id="td_8cbd_80">
                                                    <div>
                                                      <?php echo $strVenue; ?>
                                                    </div>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="20" id="td_8cbd_81">
                            &nbsp;
                        </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_82" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_59">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_60">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_83">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space.png" width="560" alt="shadow-space" id="img_8cbd_9" border="0" hspace="0" vspace="0"/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_84">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
<?php
    $iCnt++;
  endforeach;
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_85" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_61">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_62">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_86">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_87">
                            <br/>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="27" id="td_8cbd_88">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_63">
                              <tbody>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_89">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
<?php
endif;
?>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_90" bgcolor="#323537">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_64">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_65">
                      <tbody>
                        <tr>
                          <td valign="top" height="21" id="td_8cbd_91">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td valign="middle">

                            <table width="auto" align="left" border="0" cellpadding="0" cellspacing="0" class="full-width-center" id="table_8cbd_66">
                              <tbody>
                                <tr>
                                  <td align="left" id="td_8cbd_92">
                                    <div id="div_8cbd_2">
                                      &nbsp;&nbsp;
                                      <a href="{profile_url}" id="a_8cbd_4">
                                        Odhlásiť sa
                                      </a>
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          
                          <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="20" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_67">
                              <tbody>
                                <tr>
                                  <td height="1" class="h-20" id="td_8cbd_93">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" class="full-width-center" id="table_8cbd_68">
                              <tbody>
                                <tr>
                                  <td valign="middle">
                                    <table width="auto" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_69">
                                      <tbody>
                                        <tr>
                                          <td align="center" valign="middle" id="td_8cbd_94" width="25">
                                            <a href="https://www.facebook.com/salsaruedajarohluch/" id="a_8cbd_5">
                                              <img src="<?php echo $theme_url; ?>/images/fb-logo.png" width="25" alt="facebook" id="img_8cbd_10" height="auto"/>
                                            </a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="18" id="td_8cbd_95">
                          </td>
                        </tr>
                        <tr>
                          <td valign="middle">
                            <div id="div_8cbd_3">
                              Chcete pridať udalosť? Pošlite nám link Facebook udalosti na náš Facebook profil. Ďakujeme.
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="18" id="td_8cbd_96">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td valign="top" align="center" id="td_8cbd_97" class="container" bgcolor="#121212">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" id="table_8cbd_70" class="container">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_71">
                      <tbody>
                        <tr>
                          <td valign="top" height="6" id="td_8cbd_98">
                          </td>
                        </tr>
                        <tr>
                          <td valign="middle" align="center">
                            <table width="auto" align="left" border="0" cellspacing="0" cellpadding="0" class="full-width-center" id="table_8cbd_72">
                              <tbody>
                                <tr>
                                  <td align="left" id="td_8cbd_99">
                                    <div>
                                      <a href="{blog_url}" id="a_8cbd_6">
                                        © <?php echo date('Y');?> festivaly.salsarueda.dance
                                      </a>
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="20" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_73">
                              <tbody>
                                <tr>
                                  <td height="1" class="h-20" id="td_8cbd_100">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="auto" align="right" border="0" cellspacing="0" cellpadding="0" class="full-width-center" id="table_8cbd_74">
                              <tbody>
                                <tr>
                                  <td align="right" id="td_8cbd_101">
                                    <div>
                                      <?php echo date('j. ') . date_i18n( 'F' ) . date(' Y'); ?>
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="19" id="td_8cbd_102">
                            &nbsp;
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>