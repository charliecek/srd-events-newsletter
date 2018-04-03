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
  $iEndTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + 1 + 7);
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
<title>srd</title>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700,300&subset=latin,cyrillic,greek" rel="stylesheet" type="text/css">
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
  <body  style="font-size:12px; width:100%; height:100%;">
    <table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #efefef; width: 800px; max-width: 800px; outline: rgb(239, 239, 239) solid 1px; box-shadow: rgb(224, 224, 224) 0px 0px 5px; margin: 0px auto;">
      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="right" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: right; word-break: break-word; line-height: 22px;">
                            <div style="text-align: right; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="line-height: 20px; font-size: 12px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Ak sa vám Newsleter nezobrazuje správne, <span style="color: #6689ac; line-height: 20px; font-size: 12px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="{email_url}" data-mce-href="link-webbrowser" style="border-style: none; text-decoration: none !important; line-height: 20px; font-size: 12px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #6689ac; line-height: 20px; font-size: 12px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">kliknite sem.</font></span></font></a></font></span></font></span></font></div>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
      <!-- START LAYOUT-21 ( BIG IMAGE ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start big image -->
              <tbody>
                <tr>
                  <td valign="top">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" style="width: 600px;">
                            <img src="<?php echo $theme_url; ?>/images/20170126185928_news_top_1.jpg" width="600" style="max-width: 600px; display: block !important; width: 600px; height: auto;" alt="image" border="0" hspace="0" vspace="0" height="auto">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end big image -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-21 ( BIG IMAGE ) -->
      </tbody>
      <!--START LAYOUT-9 ( SHADOW SPACE ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" style="width: 560px;">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" style="max-width:560px; display:block!important;" border="0" hspace="0" vspace="0">
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="8" style="height: 8px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <!--END LAYOUT-9 ( SHADOW SPACE ) -->
      </tbody>
      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="9" style="height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="center" style="font-size: 14px; color: #888888; font-weight: 300; text-align: center; font-family: 'Open Sans', Arial, Helvetica, sans-serif; word-break: break-word; line-height: 22px;"><span style="color: #888888; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Prinášame vám aktuálne udalosti zo sveta salsy, bachaty a kizomby na nadchádzajúci týždeň a niektoré najnovšie pridané akcie.</font></span></td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="2" style="height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                  <td valign="top" style="padding-left:10px; padding-right:10px; padding-top:10px; padding-bottom:10px;" class="full-block" dup="0">
                                    <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="background-color: #6dabdb; border-radius: 3px; margin: 0px auto;">
                                      <tbody>
                                        <tr>

                                          <td width="auto" align="center" valign="middle" height="40" style="font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; padding-left: 32px; padding-right: 32px; word-break: break-word; line-height: 22px;"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="{blog_url}" data-mce-href="{blog_url}" style="border-style: none; text-decoration: none !important; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Navštíviť stránku</font></span></font></a><br style="font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></font></span></td>

                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="right" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: right; word-break: break-word; line-height: 22px;"><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>


<?php
if (count($aEventsUpcomingWeek) > 0) :
?>

      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) THIS WEEKS TITLE START -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ff7e00; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="left" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: left; word-break: break-word; line-height: 22px;">
                            <div style="text-align: left; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #2d3b4d; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 44px; font-size: 36px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Udalosti na tento týždeň</font></span><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></font></span></font></div>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="2" style="height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="4" style="height: 4px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
      <!--START LAYOUT-35 ( SHADOW SPACE ) -->
      <tr>
        <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
          <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;">
            <tbody>
              <tr>
                <td valign="top" align="center">
                  <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                    <tbody>
                      <tr>
                        <td valign="top" class="image-full-width" width="560" style="width: 560px;">
                          <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" style="max-width: 560px; display: block !important;" border="0" hspace="0" vspace="0">
                        </td>
                      </tr>
                      <!-- start space -->
                      <tr>
                        <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                        </td>
                      </tr>
                      <!-- end space -->
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <!--END LAYOUT-35 ( SHADOW SPACE ) -->

<?php
  $iCnt = 1;
  foreach ($aEventsUpcomingWeek as $oEvent) {
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
?>

      <!-- START LAYOUT-21 ( BIG IMAGE ) SINGLE EVENT START -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start big image -->
              <tbody>
                <tr>
                  <td valign="top">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" style="width: 600px;">
                            <a target="_blank"  href="<?php echo get_permalink($post); ?>">
                              <img src="<?php echo $image; ?>" width="600" style="max-width: 600px; display: block !important; width: 600px; height: auto;" alt="image" border="0" hspace="0" vspace="0" height="auto">
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end big image -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-21 ( BIG IMAGE ) -->
      </tbody>
      <!-- START LAYOUT-20 ( ICON / HEADING) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start duplicate icon/heading -->
              <tbody>
                <!-- end duplicate icon/heading -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-20 ( ICON / HEADING) -->
      </tbody>
      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="left" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: left; word-break: break-word; line-height: 22px;">
                            <div style="text-align: left; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #6dabdb; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ff6600; line-height: 44px; font-size: 36px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><?php the_title(); ?></font></span><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><span style="color: #808080; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><?php echo $strExcerpt; ?></font></span><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></font></span></font></div>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
      <!-- START LAYOUT-38 ( BUTTON / PRICE ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start group 2-col -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="9" style="height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td valign="top" align="center">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!--start 2col-->
                              <tbody>
                                <tr>
                                  <td valign="middle" class="full-block">
                                    <!-- start duplicate button -->
                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" style="margin:0 auto; mso-table-lspace:0pt; mso-table-rspace:0pt;">
                                      <tbody>
                                        <tr>
                                          <td class="clear-pad" valign="top" style="padding-left:5px; padding-right:5px; padding-top:10px; padding-bottom:10px;" dup="0">
                                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="background-color: #6dabdb; border-radius: 3px; margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td valign="middle" width="18" style="width: 18px;">
                                                  </td>
                                                  <td valign="middle" style="padding-right: 10px; width: 14px;" width="14">
                                                    <img src="<?php echo $theme_url; ?>/images/icon-arrow.png" width="14" style="max-width: 14px; display: block !important;" alt="icon-arrow" border="0" hspace="0" vspace="0">
                                                  </td>
                                                  <td width="auto" align="center" valign="middle" height="40" style="font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; padding-right: 18px; word-break: break-word; line-height: 22px;"><span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="<?php echo get_permalink(); ?>" data-mce-href="<?php echo get_permalink(); ?>" style="border-style: none; text-decoration: none !important; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Viac info</font></span></font></a></font></span></td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    <!-- end duplicate button -->
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <!--start space width -->
                                    <table width="1" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" style="min-width: 1px; height: 1px; border-spacing: 0px; width: 1px;mso-table-lspace:0pt; mso-table-rspace:0pt;">
                                      <tbody>
                                        <tr>
                                          <td height="1" width="1" class="h-20" style="display: block; width: 1px; height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    <!--end space width -->
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <!-- start col left -->
                                    <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;" class="full-width">
                                      <!-- start price -->
                                      <tbody>
                                        <tr>
                                          <td valign="middle" dup="0">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="right" valign="top" style="font-size: 14px; color: #888888; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; text-align: right; word-break: break-word; line-height: 22px;">
                                                    <span style="line-height: 22px; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                      <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                        <div style="text-align: right; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">↗ &nbsp;<?php echo $strStart; ?><br style="font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">↘ &nbsp;<?php echo $strEnd; ?></font></div>
                                                      </font>
                                                    </span>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                          <td valign="middle" dup="0">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="left" valign="top" style="font-size: 14px; color: #888888; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; text-align: left; word-break: break-word; line-height: 22px;">
                                                    <span style="line-height: 22px; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                      <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                        <div style="text-align: left; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                          <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                            <?php echo $strVenue; ?>
                                                          </font>
                                                        </div>
                                                      </font>
                                                    </span>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                        <!-- end price -->
                                      </tbody>
                                    </table>
                                    <!-- end col left -->
                                  </td>
                                </tr>
                                <!--end 2col -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end group 2-col -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-38 ( BUTTON / PRICE ) -->
      </tbody>
      <!--START LAYOUT-3 ( SHADOW SPACE ) -->
      <tr>
        <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
          <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;">
            <tbody>
              <tr>
                <td valign="top" align="center">
                  <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                    <tbody>
                      <tr>
                        <td valign="top" class="image-full-width" width="560" style="width: 560px;">
                          <img src="<?php echo $theme_url; ?>/images/shadow-space.png" width="560" alt="shadow-space" style="max-width:560px; display:block!important;" border="0" hspace="0" vspace="0">
                        </td>
                      </tr>
                      <!-- start space -->
                      <tr>
                        <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                        </td>
                      </tr>
                      <!-- end space -->
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <!--END LAYOUT-3 ( SHADOW SPACE ) -->
<?php
    $iCnt++;
  }
?>

      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="right" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: right; word-break: break-word; line-height: 22px;"><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="27" style="height: 27px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
<?php
endif;

if (count($aEventsLatest) > 0) :
?>

      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) THIS WEEKS TITLE START -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ff7e00; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="left" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: left; word-break: break-word; line-height: 22px;">
                            <div style="text-align: left; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #2d3b4d; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 44px; font-size: 36px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Nové udalosti</font></span><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></font></span></font></div>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="2" style="height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="4" style="height: 4px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
      <!--START LAYOUT-35 ( SHADOW SPACE ) -->
      <tr>
        <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
          <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;">
            <tbody>
              <tr>
                <td valign="top" align="center">
                  <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                    <tbody>
                      <tr>
                        <td valign="top" class="image-full-width" width="560" style="width: 560px;">
                          <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" style="max-width: 560px; display: block !important;" border="0" hspace="0" vspace="0">
                        </td>
                      </tr>
                      <!-- start space -->
                      <tr>
                        <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                        </td>
                      </tr>
                      <!-- end space -->
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <!--END LAYOUT-35 ( SHADOW SPACE ) -->

<?php
  $iCnt = 1;
  foreach ($aEventsLatest as $post) {
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
?>

      <!-- START LAYOUT-21 ( BIG IMAGE ) SINGLE EVENT START -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start big image -->
              <tbody>
                <tr>
                  <td valign="top">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" style="width: 600px;">
                            <a target="_blank"  href="<?php echo get_permalink($post); ?>">
                              <img src="<?php echo $image; ?>" width="600" style="max-width: 600px; display: block !important; width: 600px; height: auto;" alt="image" border="0" hspace="0" vspace="0" height="auto">
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end big image -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-21 ( BIG IMAGE ) -->
      </tbody>
      <!-- START LAYOUT-20 ( ICON / HEADING) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start duplicate icon/heading -->
              <tbody>
                <!-- end duplicate icon/heading -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-20 ( ICON / HEADING) -->
      </tbody>
      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="left" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: left; word-break: break-word; line-height: 22px;">
                            <div style="text-align: left; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #6dabdb; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ff6600; line-height: 44px; font-size: 36px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><?php the_title(); ?></font></span><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><span style="color: #808080; line-height: 22px; font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><?php echo $strExcerpt; ?></font></span><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></font></span></font></div>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
      <!-- START LAYOUT-38 ( BUTTON / PRICE ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start group 2-col -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="9" style="height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td valign="top" align="center">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!--start 2col-->
                              <tbody>
                                <tr>
                                  <td valign="middle" class="full-block">
                                    <!-- start duplicate button -->
                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" style="margin:0 auto; mso-table-lspace:0pt; mso-table-rspace:0pt;">
                                      <tbody>
                                        <tr>
                                          <td class="clear-pad" valign="top" style="padding-left:5px; padding-right:5px; padding-top:10px; padding-bottom:10px;" dup="0">
                                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="background-color: #6dabdb; border-radius: 3px; margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td valign="middle" width="18" style="width: 18px;">
                                                  </td>
                                                  <td valign="middle" style="padding-right: 10px; width: 14px;" width="14">
                                                    <img src="<?php echo $theme_url; ?>/images/icon-arrow.png" width="14" style="max-width: 14px; display: block !important;" alt="icon-arrow" border="0" hspace="0" vspace="0">
                                                  </td>
                                                  <td width="auto" align="center" valign="middle" height="40" style="font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; padding-right: 18px; word-break: break-word; line-height: 22px;"><span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="<?php echo get_permalink(); ?>" data-mce-href="<?php echo get_permalink(); ?>" style="border-style: none; text-decoration: none !important; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Viac info</font></span></font></a></font></span></td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    <!-- end duplicate button -->
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <!--start space width -->
                                    <table width="1" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" style="min-width: 1px; height: 1px; border-spacing: 0px; width: 1px;mso-table-lspace:0pt; mso-table-rspace:0pt;">
                                      <tbody>
                                        <tr>
                                          <td height="1" width="1" class="h-20" style="display: block; width: 1px; height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    <!--end space width -->
                                  </td>
                                  <td valign="middle" class="full-block">
                                    <!-- start col left -->
                                    <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;" class="full-width">
                                      <!-- start price -->
                                      <tbody>
                                        <tr>
                                          <td valign="middle" dup="0">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="right" valign="top" style="font-size: 14px; color: #888888; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; text-align: right; word-break: break-word; line-height: 22px;">
                                                    <span style="line-height: 22px; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                      <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                        <div style="text-align: right; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">↗ &nbsp;<?php echo $strStart; ?><br style="font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">↘ &nbsp;<?php echo $strEnd; ?></font></div>
                                                      </font>
                                                    </span>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                          <td valign="middle" dup="0">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-right: 1px solid rgb(231, 233, 236); padding-left: 20px; padding-right: 20px; margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="left" valign="top" style="font-size: 14px; color: #888888; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif; background-clip: padding-box; text-align: left; word-break: break-word; line-height: 22px;">
                                                    <span style="line-height: 22px; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                      <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                        <div style="text-align: left; font-size: 14px; font-weight: 600; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                          <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                            <?php echo $strVenue; ?>
                                                          </font>
                                                        </div>
                                                      </font>
                                                    </span>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                        <!-- end price -->
                                      </tbody>
                                    </table>
                                    <!-- end col left -->
                                  </td>
                                </tr>
                                <!--end 2col -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end group 2-col -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-38 ( BUTTON / PRICE ) -->
      </tbody>
      <!--START LAYOUT-3 ( SHADOW SPACE ) -->
      <tr>
        <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
          <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #eef0f3; width: 600px; margin: 0px auto;">
            <tbody>
              <tr>
                <td valign="top" align="center">
                  <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                    <tbody>
                      <tr>
                        <td valign="top" class="image-full-width" width="560" style="width: 560px;">
                          <img src="<?php echo $theme_url; ?>/images/shadow-space.png" width="560" alt="shadow-space" style="max-width:560px; display:block!important;" border="0" hspace="0" vspace="0">
                        </td>
                      </tr>
                      <!-- start space -->
                      <tr>
                        <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                        </td>
                      </tr>
                      <!-- end space -->
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <!--END LAYOUT-3 ( SHADOW SPACE ) -->
<?php
    $iCnt++;
  }
?>

      <!-- START LAYOUT-22 ( CONTENT / BUTTON ) -->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #eef0f3;" bgcolor="#eef0f3">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #eef0f3; min-width: 600px; width: 600px; margin: 0px auto;">
              <!-- start content / button -->
              <tbody>
                <tr dup="0">
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td align="right" style="font-size: 14px; color: #888888; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: right; word-break: break-word; line-height: 22px;"><br style="font-size: 14px; font-weight: 300; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"></td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="27" style="height: 27px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space --><!--start button-->
                        <tr>
                          <td valign="top">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                              <!-- start duplicate button -->
                              <tbody>
                                <tr>
                                </tr>
                                <!-- end duplicate button -->
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!--end button--><!-- start space -->
                        <tr>
                          <td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
                <!-- end content / button -->
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-22 ( CONTENT / BUTTON ) -->
      </tbody>
<?php
endif;
?>
      <!--START LAYOUT-52 ( FOOTER )-->
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" style="background-color: #323537;" bgcolor="#323537">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #323537; width: 600px; margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="21" style="height: 21px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td valign="middle">

                            <table width="auto" align="left" border="0" cellpadding="0" cellspacing="0" class="full-width-center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;">
                              <tbody>
                                <tr>
                                  <td align="left" style="font-size: 14px; color: #888888; font-weight: normal; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: left; word-break: break-word; line-height: 22px;">
                                    <div style="text-align: left; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #888888; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"> <span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">&nbsp;&nbsp;<span style="line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="{profile_url}" data-mce-href="link-unsubscribe" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">Odhlásiť sa</font></span></font></a></font></span></font></span></font></span><a href="#" style="color: #ff900e; text-decoration: none !important; border-style: none; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" data-mce-href="#" border="0"></a></font></span></font></div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          
                          <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="20" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" style="height: 1px; border-spacing: 0px; width: 20px;mso-table-lspace:0pt; mso-table-rspace:0pt;">
                              <tbody>
                                <tr>
                                  <td height="1" class="h-20" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" class="full-width-center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;">
                              <tbody>
                                <tr>
                                  <td valign="middle">
                                    <table width="auto" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
                                      <tbody>
                                        <tr>
                                          <td align="center" valign="middle" style="padding-left: 3px; padding-right: 3px; width: 25px;" width="25">
                                            <a href="https://www.facebook.com/salsaruedajarohluch/" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                            <img src="<?php echo $theme_url; ?>/images/fb-logo.png" width="25" alt="facebook" style="max-width: 25px; height: auto; display: block !important;" height="auto"></a>
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
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="18" style="height: 18px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td valign="middle">
                            <div style="text-align: left; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                              <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                <span style="color: #888888; text-decoration: none; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                  <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                    <span style="line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                      <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                        <span style="color: #ffffff; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                          <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                            <span style="line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                              <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                <font face="'Open Sans', Arial, Helvetica, sans-serif">
                                                  <span style="color: #ffffff; line-height: 21px; font-size: 13px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
                                                    <font face="'Open Sans', Arial, Helvetica, sans-serif">Chcete pridať udalosť? Pošlite nám link Facebook udalosti na náš Facebook profil. Ďakujeme.</font>
                                                  </span>
                                                </font>
                                              </font>
                                            </span>
                                          </font>
                                        </span>
                                      </font>
                                    </span>
                                  </font>
                                </span>
                              </font>
                            </div>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="18" style="height: 18px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!--END LAYOUT-52 ( FOOTER )-->
      </tbody>
      <!-- START LAYOUT-53 ( UNSUBSCRIBE )-->
      <tbody>
        <tr>
          <td valign="top" align="center" style="background-color: #121212;" class="container" bgcolor="#121212">
            <!-- start container -->
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #121212; min-width: 600px; width: 600px; margin: 0px auto;" class="container">
              <tbody>
                <tr>
                  <td valign="top" align="center">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" style="table-layout: fixed; width: 560px; margin: 0px auto;">
                      <!-- start space -->
                      <tbody>
                        <tr>
                          <td valign="top" height="6" style="height: 6px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr>
                        <!-- end space -->
                        <tr>
                          <td valign="middle" align="center">
                            <table width="auto" align="left" border="0" cellspacing="0" cellpadding="0" class="full-width-center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;">
                              <tbody>
                                <tr>
                                  <td align="left" style="font-size: 14px; color: #ffffff; font-weight: normal; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: left; word-break: break-word; line-height: 22px;">
                                    <div style="text-align: left; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #999999; text-decoration: none; line-height: 20px; font-size: 12px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="{blog_url}" style="color: #ffffff; text-decoration: none !important; border-style: none; line-height: 20px; font-size: 12px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" data-mce-href="#" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 22px; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif">© <?php echo date('Y');?> festivaly.salsarueda.dance</font></span></font></a></font></span></font></div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="20" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" style="height: 1px; border-spacing: 0px; width: 20px;mso-table-lspace:0pt; mso-table-rspace:0pt;">
                              <tbody>
                                <tr>
                                  <td height="1" class="h-20" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="auto" align="right" border="0" cellspacing="0" cellpadding="0" class="full-width-center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;">
                              <tbody>
                                <tr>
                                  <td align="right" style="font-size: 14px; color: #ffffff; font-weight: normal; font-family: 'Open Sans', Arial, Helvetica, sans-serif; text-align: right; word-break: break-word; line-height: 22px;">
                                    <div style="text-align: right; font-size: 14px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 20px; font-size: 12px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><a href="link-unsubscribe" data-mce-href="link-unsubscribe" style="border-style: none; text-decoration: none !important; line-height: 20px; font-size: 12px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;" border="0"><font face="'Open Sans', Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 20px; font-size: 12px; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif;"><font face="'Open Sans', Arial, Helvetica, sans-serif"><?php echo date('j. ') . date_i18n( 'F' ) . date(' Y'); ?></font></span></font></a></font></span></font></div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <!-- start space -->
                        <tr>
                          <td valign="top" height="19" style="height: 19px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr>
                        <!-- end space -->
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- end container -->
          </td>
        </tr>
        <!-- END LAYOUT-53 ( UNSUBSCRIBE )-->
      </tbody>
    </table>
  </body>
</html>