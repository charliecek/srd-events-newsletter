<?php
/*
 * Name: .SRD Events Newsletter.
 */

global $newsletter; // Newsletter object
global $post; // Current post managed by WordPress

if (!defined('ABSPATH'))
    exit;

/*
 * Some variables are prepared by Newsletter Plus and are available inside the theme,
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

$aOrderedCategories = explode(",", $theme_options['theme_category_order']);
// var_dump($aOrderedCategories);
$iDayOfWeekFrom = 1; // Monday

$aTermArgs = array(
  'fields'      => 'ids',
);

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
//   echo "start_date is set";
  $dtStartDate = date_create_from_format( 'j.n.Y', $theme_options['theme_start_date'] );
  date_time_set($dtStartDate, 24, 0, 0);
  $iStartTimestamp = date_timestamp_get($dtStartDate);
} else {
  $iStartTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + $iDayOfWeekFrom);
  $theme_options['theme_start_date'] = date( 'j.n.Y', $iStartTimestamp );
}
if (isset($theme_options['theme_end_date']) && !empty($theme_options['theme_end_date'])) {
  $dtEndDate = date_create_from_format( 'j.n.Y', $theme_options['theme_end_date'] );
  date_time_set($dtEndDate, 24, 0, 0);
  $iEndTimestamp = date_timestamp_get($dtEndDate);
} else {
  $iEndTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + $iDayOfWeekFrom + 7) - 1;
  $theme_options['theme_end_date'] = date( 'j.n.Y', $iEndTimestamp );
}
$theme_subject = "Newsletter " . $theme_options['theme_start_date'] . " - " . $theme_options['theme_end_date'];

// gets time
$start_time = $ai1ec_registry->get( 'date.time', $iStartTimestamp, 'sys.default' );
$end_time = $ai1ec_registry->get( 'date.time', $iEndTimestamp, 'sys.default' );

$aEventsUpcomingWeekAll = $search->get_events_between($start_time, $end_time);
// $aEventsUpcomingWeekAll = $search->get_events_between($start_time, $end_time, array(), true);

$aSelectedCategoryIds = $theme_options['theme_categories'];
$aSelectedTagIds = $theme_options['theme_tags'];
if (empty($aSelectedCategoryIds)) { $aSelectedCategoryIds = array(); }
if (empty($aSelectedTagIds)) { $aSelectedTagIds = array(); }
// var_dump($aSelectedCategoryIds);

if ($theme_options['theme_orderby'] === 'category') {
  // Ordered by category //
  $aEventsUpcomingByCats = array();
  foreach ($aOrderedCategories as $strCatSlug) {
    $aEventsUpcomingByCats[$strCatSlug] = array();
  }

  $iCnt = 1;
  $aUpcomingWeekPostIDs = array();
  foreach ($aEventsUpcomingWeekAll as $oEvent) {
    if ($iCnt > $filtersUpcomingWeek['posts_per_page']) {
      break;
    }
    $iPostID = $oEvent->get( 'post_id' );
    $oPost = $oEvent->get( 'post' );
    if ($oPost->post_status !== 'publish') {
  //     echo $iPostID.": post_status: ".$oPost->post_status . "<br>" .PHP_EOL;
      continue;
    }
    $aEventCategoryIds = wp_get_post_terms( $iPostID, 'events_categories', $aTermArgs );
    $aEventCategoriesIntersectSelected = array_intersect( $aEventCategoryIds, $aSelectedCategoryIds );
    if (empty($aEventCategoriesIntersectSelected)) {
      continue;
    }
    $aEventTagIds = wp_get_post_terms( $iPostID, 'events_tags', $aTermArgs );
    $aEventTagsIntersectSelected = array_intersect( $aEventTagIds, $aSelectedTagIds );
    if (empty($aEventTagsIntersectSelected)) {
      continue;
    }
    $aEventCategoryIds = wp_get_post_terms( $iPostID, 'events_categories', $aTermArgs );
    $aEventCategoriesIntersectSelected = array_intersect( $aEventCategoryIds, $aSelectedCategoryIds );
    if (empty($aEventCategoriesIntersectSelected)) {
      continue;
    }
    $aEventTagIds = wp_get_post_terms( $iPostID, 'events_tags', $aTermArgs );
    $aEventTagsIntersectSelected = array_intersect( $aEventTagIds, $aSelectedTagIds );
    if (empty($aEventTagsIntersectSelected)) {
      continue;
    }
    $aUpcomingWeekPostIDs[] = $iPostID;
    $aEventCategories = wp_get_post_terms( $iPostID, 'events_categories' );
    if (!empty($aEventCategories)) {
      foreach ( $aEventCategories as $oEventCategory ) {
        $bHasCat = false;
        foreach ( $aOrderedCategories as $strCatSlug ) {
          if ( $oEventCategory->slug === $strCatSlug ) {
            $aEventsUpcomingByCats[$strCatSlug][] = $oEvent;
            $bHasCat = true;
            break 2; // Use the first matched category, don't put an event into multiple items of $aEventsUpcomingByCats //
          }
        }
        if (!$bHasCat) {
          $aEventsUpcomingByCats['-'][] = $oEvent;
        }
      }
    } else {
      $aEventsUpcomingByCats['-'][] = $oEvent;
    }
    $iCnt++;
  }
  
  $aEventsUpcomingWeek = array();
  foreach ($aEventsUpcomingByCats as $aEventsIncat) {
    $aEventsUpcomingWeek = array_merge( $aEventsUpcomingWeek, $aEventsIncat);
  }
  // echo "<pre>";
  // foreach ($aEventsUpcomingByCats as $strCatSlug => $aEvents) {
  //   var_dump($strCatSlug, count($aEvents));
  // }
  // echo "</pre>";
} else {
  // Ordered by date //
  $iCnt = 1;
  $aUpcomingWeekPostIDs = array();
  foreach ($aEventsUpcomingWeekAll as $oEvent) {
    if ($iCnt > $filtersUpcomingWeek['posts_per_page']) {
      break;
    }
    $iPostID = $oEvent->get( 'post_id' );
    $oPost = $oEvent->get( 'post' );
    if ($oPost->post_status !== 'publish') {
  //     echo $iPostID.": post_status: ".$oPost->post_status . "<br>" .PHP_EOL;
      continue;
    }
    $aEventCategoryIds = wp_get_post_terms( $iPostID, 'events_categories', $aTermArgs );
    $aEventCategoriesIntersectSelected = array_intersect( $aEventCategoryIds, $aSelectedCategoryIds );
    if (empty($aEventCategoriesIntersectSelected)) {
      continue;
    }
    $aEventTagIds = wp_get_post_terms( $iPostID, 'events_tags', $aTermArgs );
    $aEventTagsIntersectSelected = array_intersect( $aEventTagIds, $aSelectedTagIds );
    if (empty($aEventTagsIntersectSelected)) {
      continue;
    }
    $aEventCategoryIds = wp_get_post_terms( $iPostID, 'events_categories', $aTermArgs );
    $aEventCategoriesIntersectSelected = array_intersect( $aEventCategoryIds, $aSelectedCategoryIds );
    if (empty($aEventCategoriesIntersectSelected)) {
      continue;
    }
    $aEventTagIds = wp_get_post_terms( $iPostID, 'events_tags', $aTermArgs );
    $aEventTagsIntersectSelected = array_intersect( $aEventTagIds, $aSelectedTagIds );
    if (empty($aEventTagsIntersectSelected)) {
      continue;
    }
    $aUpcomingWeekPostIDs[] = $iPostID;
    $aEventsUpcomingWeek[] = $oEvent;
    $iCnt++;
  }
}
/* Upcoming events - end */
/* Latest events */
$filtersLatest = array();
$iLatestEventsAddedDayOfWeekFrom = 1; // Monday
if (isset($theme_options['theme_start_date_added_latest']) && !empty($theme_options['theme_start_date_added_latest'])) {
  $dtAddedFromDate = date_create_from_format( 'j.n.Y G:i', $theme_options['theme_start_date_added_latest'] );
  $iLatestEventsDefaultAddedStartTimestamp = date_timestamp_get($dtAddedFromDate);
} else {
  $iLatestEventsDefaultAddedStartTimestamp = mktime(12, 0, 0, date("n"), date("j") - date("N") - 7 + $iLatestEventsAddedDayOfWeekFrom);
  $theme_options['theme_start_date_added_latest'] = date( 'j.n.Y G:i', $iLatestEventsDefaultAddedStartTimestamp );
}
$iMaxEventsLatest = (int) $theme_options['theme_max_events_latest'];
if ($iMaxEventsLatest == 0) {
  $iMaxEventsLatest = 10;
}
$filtersLatest['posts_per_page'] = $iMaxEventsLatest * 3;
$filtersLatest['post_type'] = array('ai1ec_event');
if (!empty($aUpcomingWeekPostIDs)) {
  $filtersLatest['exclude'] = $aUpcomingWeekPostIDs;
}
$aEventsLatestPosts = get_posts($filtersLatest);
// var_dump(count($aEventsLatestPosts));
foreach ($aEventsLatestPosts as $oPost) {
  $iPostID = $oPost->ID;
  $oEvent = new Ai1ec_Event( $ai1ec_registry );
  $oEvent->initialize_from_id( $iPostID );
  $iEventStartTimestamp = intval( $oEvent->get( 'start' )->format('U') );
  $aEventsLatestByStart[$iEventStartTimestamp] = $oPost;
}
ksort($aEventsLatestByStart);
// var_dump(count($aEventsLatestByStart));

if ($theme_options['theme_orderby'] === 'category') {
  // Ordered by category //
  $aEventsLatestByCats = array();
  foreach ($aOrderedCategories as $strCatSlug) {
    $aEventsLatestByCats[$strCatSlug] = array();
  }

  $iCnt = 1;
  foreach ($aEventsLatestByStart as $oPost) {
    if ($iCnt > $iMaxEventsLatest) {
      break;
    }
    $iPostID = $oPost->ID;
    $oEvent = new Ai1ec_Event( $ai1ec_registry );
    $oEvent->initialize_from_id( $iPostID );
    $iEventStartTimestamp = intval( $oEvent->get( 'start' )->format('U') );
    if ($iEventStartTimestamp < time()) {
      continue;
    }
    $iPostAdded = get_post_time('U', false, $oPost );
  //   echo get_post_time('j.n.Y G:i', false, $oPost )."<br>".PHP_EOL;
    if ($iPostAdded < $iLatestEventsDefaultAddedStartTimestamp) {
  //     echo "skipped<br>".PHP_EOL;
      continue;
    }

    $aEventCategories = wp_get_post_terms( $iPostID, 'events_categories' );
    if (!empty($aEventCategories)) {
      foreach ( $aEventCategories as $oEventCategory ) {
        $bHasCat = false;
        foreach ( $aOrderedCategories as $strCatSlug ) {
          if ( $oEventCategory->slug === $strCatSlug ) {
            $aEventsLatestByCats[$strCatSlug][] = $oPost;
            $bHasCat = true;
            break 2; // Use the first matched category, don't put an event into multiple items of $aEventsUpcomingByCats //
          }
        }
        if (!$bHasCat) {
          $aEventsLatestByCats['-'][] = $oPost;
        }
      }
    } else {
      $aEventsLatestByCats['-'][] = $oPost;
    }
    $iCnt++;
  }
  $aEventsLatest = array();
  foreach ($aEventsLatestByCats as $aEventsIncat) {
    $aEventsLatest = array_merge( $aEventsLatest, $aEventsIncat);
  }

  // echo "<pre>";
  // foreach ($aEventsLatestByCats as $strCatSlug => $aPosts) {
  //   var_dump($strCatSlug, count($aPosts));
  // }
  // echo "</pre>";
} else {
  // Ordered by date //
  $iCnt = 1;
  foreach ($aEventsLatestByStart as $oPost) {
    if ($iCnt > $iMaxEventsLatest) {
      break;
    }
    $iPostID = $oPost->ID;
    $oEvent = new Ai1ec_Event( $ai1ec_registry );
    $oEvent->initialize_from_id( $iPostID );
    $iEventStartTimestamp = intval( $oEvent->get( 'start' )->format('U') );
    if ($iEventStartTimestamp < time()) {
      continue;
    }
    $iPostAdded = get_post_time('U', false, $oPost );
  //   echo get_post_time('j.n.Y G:i', false, $oPost )."<br>".PHP_EOL;
    if ($iPostAdded < $iLatestEventsDefaultAddedStartTimestamp) {
  //     echo "skipped<br>".PHP_EOL;
      continue;
    }
    $aEventsLatest[] = $oPost;
    $iCnt++;
  }
}
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
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" style="width: 100%;">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="format-detection" content="telephone=no">
  <title><?php echo $theme_subject; ?></title>
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700,300&amp;subset=latin,cyrillic,greek" rel="stylesheet" type="text/css">
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
  <body style="width: 100%;height: 100%;background-color: #efefef;margin: 0;padding: 0;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;font-size: 12px;font-family: 'Open Sans', Arial, Helvetica, sans-serif;">
    <table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #efefef;width: 800px;max-width: 800px;outline: rgb(239, 239, 239) solid 1px;box-shadow: rgb(224, 224, 224) 0px 0px 5px;margin: 0px auto;">
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_0" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_0" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #eef0f3;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_1" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_1" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 3px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_2" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: right;word-break: break-word;line-height: 22px;">
                            <div id="div_8cbd_0" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;text-align: right;font-size: 14px;">
                                <span id="span_8cbd_0" style="line-height: 20px;font-size: 12px;">
                                    Ak sa vám Newsleter nezobrazuje správne, 
                                    <a href="{email_url}" id="a_8cbd_0" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;color: #6689ac;border-style: none;text-decoration: none !important;">
                                      kliknite sem.
                                    </a>
                                </span>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_2" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_4" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_5" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" id="table_8cbd_3" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_4" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td align="center" valign="middle" class="image-full-width" width="600" id="td_8cbd_6" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 600px;">
                            <img src="<?php echo $theme_url; ?>/images/20170126185928_news_top_1.jpg" width="600" id="img_8cbd_0" alt="image" border="0" hspace="0" vspace="0" height="auto" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 600px;width: 600px;height: auto !important;display: block !important;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_7" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_5" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;min-width: 600px;background-color: #eef0f3;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_6" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_8" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 560px;">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" id="img_8cbd_1" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 560px;height: auto !important;display: block!important;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="8" id="td_8cbd_9" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 8px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_10" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_7" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_8" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="9" id="td_8cbd_11" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 9px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="center" id="td_8cbd_12" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: center;word-break: break-word;line-height: 22px;">
                            <span id="span_8cbd_1" style="text-decoration: none;">
                                Prinášame vám aktuálne udalosti zo sveta salsy, bachaty a kizomby na nadchádzajúci týždeň a niektoré najnovšie pridané akcie.
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="2" id="td_8cbd_13" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 2px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_9" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td valign="top" id="td_8cbd_14" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;padding-left: 10px;padding-right: 10px;padding-top: 10px;padding-bottom: 10px;">
                                    <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_10" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #6dabdb;border-radius: 3px;margin: 0px auto;">
                                      <tbody>
                                        <tr>

                                          <td width="auto" align="center" valign="middle" height="40" id="td_8cbd_15" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #ffffff;font-weight: normal;text-align: center;background-clip: padding-box;padding-left: 32px;padding-right: 32px;word-break: break-word;line-height: 22px;">
                                            <a href="{blog_url}" id="a_8cbd_1" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-style: none;font-weight: 400;color: #ffffff;text-decoration: none !important;">
                                              Navštíviť stránku
                                            </a>
                                            <br style="line-height: 100%;">
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
                          <td valign="top" height="1" id="td_8cbd_16" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_17" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_11" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #eef0f3;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_12" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_18" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 3px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_19" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: right;word-break: break-word;line-height: 22px;">
                            <br style="line-height: 100%;">
                              </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_20" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_13" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_21" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_22" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_14" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ff7e00;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_15" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_23" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 3px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_24" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: left;word-break: break-word;line-height: 22px;">
                            <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
                              <span id="span_8cbd_2" style="text-decoration: none;color: #ffffff;line-height: 44px;font-size: 36px;">
                                  Udalosti na tento týždeň
                              </span>
                              <br style="line-height: 100%;">
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="2" id="td_8cbd_25" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 2px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_16" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="4" id="td_8cbd_26" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 4px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_27" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_17" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;min-width: 600px;background-color: #eef0f3;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_18" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_28" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 560px;">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" id="img_8cbd_2" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 560px;height: auto !important;display: block !important;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_29" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
  foreach ($aEventsUpcomingWeek as $oEvent) :
    $tsStart = $oEvent->get( 'start' );
    $strStart = $ai1ec_registry->get('view.event.time')->get_long_date($tsStart);
    $tsEnd = $oEvent->get( 'end' );
    $strEnd = $ai1ec_registry->get('view.event.time')->get_long_date($tsEnd);
    $post = $oEvent->get( 'post' );
    setup_postdata($post);
//     $image = nt_post_image(get_the_ID(), 'thumbnail');
//     $image = get_the_post_thumbnail( null );
    $aImage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' );
    $image = "";
    if (!empty($aImage)) {
      $image = $aImage[0];
    }
    $strVenue = ai1ecf_fix_location($oEvent->get( 'venue' ), $post->ID, $oEvent->get("address"), $oEvent->get("contact_name"), true);
    
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

      <?php if (!empty($image)) { ?>
        <tbody>
          <tr>
            <td align="center" valign="top" class="container" id="td_8cbd_30" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
              <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" id="table_8cbd_19" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
                <tbody>
                  <tr>
                    <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                      <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_20" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                        <tbody>
                          <tr>
                            <td align="center" valign="middle" class="image-full-width" width="600" id="td_8cbd_31" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 600px;">
                              <!-- PostID: <?php echo $post->ID; ?> -->
                              <a target="_blank" href="<?php echo get_permalink($post); ?>" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;text-decoration: none !important;">
                                <img src="<?php echo $image; ?>" width="600" id="img_8cbd_3" alt="image" border="0" hspace="0" vspace="0" height="auto" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 600px;width: 600px;height: auto !important;border: 0 !important;display: block !important;">
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
      <?php } ?>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_32" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_21" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_33" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_22" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_23" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="20" id="td_8cbd_34" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 20px;font-size: 0px;line-height: 0;">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_35" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: left;word-break: break-word;line-height: 22px;">
                            <div id="div_8cbd_1" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;text-align: left;font-size: 14px;font-weight: 300;">
                                <span id="span_8cbd_3" style="text-decoration: none;">
                                    <span id="span_8cbd_4" style="color: #ff6600;line-height: 44px;font-size: 36px;">
                                        <?php the_title(); ?>
                                    </span>
                                    <br style="line-height: 100%;">
                                    <span id="span_8cbd_5" style="color: #808080;">
                                        <?php echo $strExcerpt; ?>
                                    </span>
                                    <br style="line-height: 100%;">
                                </span>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_36" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_24" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_37" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_38" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_25" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_26" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="9" id="td_8cbd_39" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 9px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_27" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td valign="middle" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 25%;">
                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" id="table_8cbd_28" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0 auto;">
                                      <tbody>
                                        <tr>
                                          <td class="clear-pad" valign="top" id="td_8cbd_40" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;padding-left: 5px;padding-right: 5px;padding-top: 10px;padding-bottom: 10px;">
                                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_29" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #6dabdb;border-radius: 3px;margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td valign="middle" width="18" id="td_8cbd_41" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 18px;">
                                                  </td>
                                                  <td valign="middle" id="td_8cbd_42" width="14" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;padding-right: 10px;width: 14px;">
                                                    <img src="<?php echo $theme_url; ?>/images/icon-arrow.png" width="14" id="img_8cbd_4" alt="icon-arrow" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 14px;height: auto !important;display: block !important;">
                                                  </td>
                                                  <td width="auto" align="center" valign="middle" height="40" id="td_8cbd_43" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #ffffff;font-weight: normal;text-align: center;background-clip: padding-box;padding-right: 18px;word-break: break-word;line-height: 22px;">
                                                    <a href="<?php echo get_permalink($post); ?>" id="a_8cbd_2" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-style: none;font-weight: 400;color: #ffffff;text-decoration: none !important;">
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
                                  <td valign="middle" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 0;">
                                    <table width="1" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_30" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0px;min-width: 1px;height: 1px;width: 1px;">
                                      <tbody>
                                        <tr>
                                          <td height="1" width="1" class="h-20" id="td_8cbd_44" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;display: block;width: 1px;height: 1px;font-size: 0px;line-height: 0;">
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="middle" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 74%;">
                                    <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_31" class="full-width" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0 auto; width: 100%;">
                                      <tbody>
                                        <tr>
                                          <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 50%;">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_32" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;border-right: 1px solid rgb(231, 233, 236);padding-left: 20px;padding-right: 20px;margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="right" valign="top" id="td_8cbd_45" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 600;background-clip: padding-box;text-align: right;word-break: break-word;line-height: 22px;">
                                                    <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">↗ &nbsp;<?php echo $strStart; ?><br style="line-height: 100%;">↘ &nbsp;<?php echo $strEnd; ?></div>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                          <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 49%;">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_33" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;border-right: 1px solid rgb(231, 233, 236);padding-left: 20px;padding-right: 20px;margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="left" valign="top" id="td_8cbd_46" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 600;background-clip: padding-box;text-align: left;word-break: break-word;line-height: 22px;">
                                                    <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
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
                          <td valign="top" height="20" id="td_8cbd_47" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 20px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_48" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_34" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;min-width: 600px;background-color: #eef0f3;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_35" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_49" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 560px;">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space.png" width="560" alt="shadow-space" id="img_8cbd_5" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 560px;height: auto !important;display: block!important;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_50" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
  endforeach;
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_51" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_36" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #eef0f3;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_37" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_52" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 3px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_53" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: right;word-break: break-word;line-height: 22px;">
                            <br style="line-height: 100%;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="27" id="td_8cbd_54" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 27px;font-size: 0px;line-height: 0;">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_38" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_55" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_56" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_39" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ff7e00;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_40" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_57" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 3px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_58" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: left;word-break: break-word;line-height: 22px;">
                            <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
                              <span id="span_8cbd_6" style="text-decoration: none;color: #ffffff;line-height: 44px;font-size: 36px;">
                                Nové udalosti
                              </span>
                              <br style="line-height: 100%;">
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="2" id="td_8cbd_59" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 2px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_41" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="4" id="td_8cbd_60" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 4px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_61" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_42" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;min-width: 600px;background-color: #eef0f3;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_43" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_62" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 560px;">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space2.png" width="560" alt="shadow-space" id="img_8cbd_6" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 560px;height: auto !important;display: block !important;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_63" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
  foreach ($aEventsLatest as $post) :
    $oEvent = ai1ecf_get_event_by_post_id( $post->ID );
    $tsStart = $oEvent->get( 'start' );
    $strStart = $ai1ec_registry->get('view.event.time')->get_long_date($tsStart);
    $tsEnd = $oEvent->get( 'end' );
    $strEnd = $ai1ec_registry->get('view.event.time')->get_long_date($tsEnd);
    setup_postdata($post);

    $aImage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' );
    $image = "";
    if (!empty($aImage)) {
      $image = $aImage[0];
    }
    $strVenue = ai1ecf_fix_location($oEvent->get( 'venue' ), $post->ID, $oEvent->get("address"), $oEvent->get("contact_name"), true);
    
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

      <?php if (!empty($image)) { ?>
        <tbody>
          <tr>
            <td align="center" valign="top" class="container" id="td_8cbd_64" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
              <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" id="table_8cbd_44" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
                <tbody>
                  <tr>
                    <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                      <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_45" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                        <tbody>
                          <tr>
                            <td align="center" valign="middle" class="image-full-width" width="600" id="td_8cbd_65" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 600px;">
                              <a target="_blank" href="<?php echo get_permalink($post); ?>" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;text-decoration: none !important;">
                                <img src="<?php echo $image; ?>" width="600" id="img_8cbd_7" alt="image" border="0" hspace="0" vspace="0" height="auto" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 600px;width: 600px;height: auto !important;border: 0 !important;display: block !important;">
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
      <?php } ?>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_66" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_46" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_67" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_47" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_48" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="20" id="td_8cbd_68" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 20px;font-size: 0px;line-height: 0;">
                            &nbsp;
                        </td>
                        </tr>
                        <tr>
                          <td align="left" id="td_8cbd_69" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: left;word-break: break-word;line-height: 22px;">
                            <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
                                <span id="span_8cbd_7" style="text-decoration: none;">
                                    <span id="span_8cbd_8" style="color: #ff6600;line-height: 44px;font-size: 36px;">
                                        <?php the_title(); ?>
                                    </span>
                                    <br style="line-height: 100%;">
                                    <span id="span_8cbd_9" style="color: #808080;">
                                        <?php echo $strExcerpt; ?>
                                    </span>
                                    <br style="line-height: 100%;">
                                </span>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_70" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_49" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_71" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_72" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_50" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #ffffff;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_51" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="9" id="td_8cbd_73" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 9px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_52" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td valign="middle" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 25%;">
                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" id="table_8cbd_53" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0 auto;">
                                      <tbody>
                                        <tr>
                                          <td class="clear-pad" valign="top" id="td_8cbd_74" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;padding-left: 5px;padding-right: 5px;padding-top: 10px;padding-bottom: 10px;">
                                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_54" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #6dabdb;border-radius: 3px;margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td valign="middle" width="18" id="td_8cbd_75" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 18px;">
                                                  </td>
                                                  <td valign="middle" id="td_8cbd_76" width="14" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;padding-right: 10px;width: 14px;">
                                                    <img src="<?php echo $theme_url; ?>/images/icon-arrow.png" width="14" id="img_8cbd_8" alt="icon-arrow" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 14px;height: auto !important;display: block !important;">
                                                  </td>
                                                  <td width="auto" align="center" valign="middle" height="40" id="td_8cbd_77" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #ffffff;font-weight: normal;text-align: center;background-clip: padding-box;padding-right: 18px;word-break: break-word;line-height: 22px;">
                                                    <a href="<?php echo get_permalink($post); ?>" id="a_8cbd_3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-style: none;font-weight: 400;color: #ffffff;text-decoration: none !important;">
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
                                  <td valign="middle" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 0;">
                                    <table width="1" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_55" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0px;min-width: 1px;height: 1px;width: 1px;">
                                      <tbody>
                                        <tr>
                                          <td height="1" width="1" class="h-20" id="td_8cbd_78" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;display: block;width: 1px;height: 1px;font-size: 0px;line-height: 0;">
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="middle" class="full-block" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 74%;">
                                    <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_56" class="full-width" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0 auto; width: 100%;">
                                      <tbody>
                                        <tr>
                                          <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 50%;">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_57" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;border-right: 1px solid rgb(231, 233, 236);padding-left: 20px;padding-right: 20px;margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="right" valign="top" id="td_8cbd_79" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 600;background-clip: padding-box;text-align: right;word-break: break-word;line-height: 22px;">
                                                    <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">↗ &nbsp;<?php echo $strStart; ?><br style="line-height: 100%;">↘ &nbsp;<?php echo $strEnd; ?></div>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                          <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse; width: 49%;">
                                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_58" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;border-right: 1px solid rgb(231, 233, 236);padding-left: 20px;padding-right: 20px;margin: 0px auto;">
                                              <tbody>
                                                <tr>
                                                  <td width="auto" align="left" valign="top" id="td_8cbd_80" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 600;background-clip: padding-box;text-align: left;word-break: break-word;line-height: 22px;">
                                                    <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
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
                          <td valign="top" height="20" id="td_8cbd_81" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 20px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_82" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_59" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;min-width: 600px;background-color: #eef0f3;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_60" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" class="image-full-width" width="560" id="td_8cbd_83" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;width: 560px;">
                            <img src="<?php echo $theme_url; ?>/images/shadow-space.png" width="560" alt="shadow-space" id="img_8cbd_9" border="0" hspace="0" vspace="0" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 560px;height: auto !important;display: block!important;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_84" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
  endforeach;
?>

      <tbody>
        <tr>
          <td align="center" valign="top" class="container" id="td_8cbd_85" bgcolor="#eef0f3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #eef0f3;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_61" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #eef0f3;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_62" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="3" id="td_8cbd_86" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 3px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td align="right" id="td_8cbd_87" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: 300;text-align: right;word-break: break-word;line-height: 22px;">
                            <br style="line-height: 100%;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="27" id="td_8cbd_88" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 27px;font-size: 0px;line-height: 0;">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" id="table_8cbd_63" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                              <tbody>
                                <tr>
                                  <td style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="1" id="td_8cbd_89" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
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
          <td align="center" valign="top" class="container" id="td_8cbd_90" bgcolor="#323537" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #323537;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" id="table_8cbd_64" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;min-width: 600px;background-color: #323537;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_65" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="21" id="td_8cbd_91" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 21px;font-size: 0px;line-height: 0;">
                            &nbsp;
                          </td>
                        </tr>
                        <tr>
                          <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">

                            <table width="auto" align="right" border="0" cellpadding="0" cellspacing="0" class="full-width-center" id="table_8cbd_66" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;">
                              <tbody>
                                <tr>
                                  <td align="right" id="td_8cbd_92" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #888888;font-weight: normal;text-align: right;word-break: break-word;line-height: 22px;">
                                    <div id="div_8cbd_2" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;font-weight: 400;text-decoration: none;color: #ffffff;">
                                      &nbsp;&nbsp;
                                      <a href="{profile_url}" id="a_8cbd_4" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-style: none;line-height: 21px;font-size: 13px;color: #ffffff;text-decoration: none !important;">
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
                            <table width="auto" align="left" border="0" cellpadding="0" cellspacing="0" class="full-width-center" id="table_8cbd_68" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;">
                              <tbody>
                                <tr>
                                  <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                                    <table width="auto" align="center" border="0" cellpadding="0" cellspacing="0" id="table_8cbd_69" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;margin: 0px auto;">
                                      <tbody>
                                        <tr>
                                          <td align="center" valign="middle" id="td_8cbd_94" width="25" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;padding-left: 3px;padding-right: 3px;width: 25px;">
                                            <a href="https://www.facebook.com/salsaruedajarohluch/" id="a_8cbd_5" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;font-size: inherit;border-style: none;text-decoration: none !important;">
                                              <img src="<?php echo $theme_url; ?>/images/fb-logo.png" width="25" alt="facebook" id="img_8cbd_10" height="auto" style="line-height: 100%;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;max-width: 25px;height: auto !important;border: 0 !important;display: block !important;">
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
                          <td valign="top" height="18" id="td_8cbd_95" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 18px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="middle" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <div id="div_8cbd_3" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;text-align: left;font-size: 13px;font-weight: 400;text-decoration: none;line-height: 21px;color: #ffffff;">
                              Chcete pridať udalosť? Pošlite nám link Facebook udalosti na náš Facebook profil. Ďakujeme.
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="18" id="td_8cbd_96" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 18px;font-size: 0px;line-height: 0;">
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
          <td valign="top" align="center" id="td_8cbd_97" class="container" bgcolor="#121212" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;background-color: #121212;">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" id="table_8cbd_70" class="container" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;background-color: #121212;min-width: 600px;width: 600px;margin: 0px auto;">
              <tbody>
                <tr>
                  <td valign="top" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="full-width" id="table_8cbd_71" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;table-layout: fixed;width: 560px;margin: 0px auto;">
                      <tbody>
                        <tr>
                          <td valign="top" height="6" id="td_8cbd_98" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 6px;font-size: 0px;line-height: 0;">
                          </td>
                        </tr>
                        <tr>
                          <td valign="middle" align="center" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;">
                            <table width="auto" align="left" border="0" cellspacing="0" cellpadding="0" class="full-width-center" id="table_8cbd_72" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;">
                              <tbody>
                                <tr>
                                  <td align="left" id="td_8cbd_99" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;font-size: 14px;color: #ffffff;font-weight: normal;text-align: left;word-break: break-word;line-height: 22px;">
                                    <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
                                      <a href="{blog_url}" id="a_8cbd_6" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;color: #999999;border-style: none;text-decoration: none !important;">
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
                            <table width="20" border="0" cellpadding="0" cellspacing="0" align="left" class="full-width" id="table_8cbd_73" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0px;height: 1px;width: 20px;">
                              <tbody>
                                <tr>
                                  <td height="1" class="h-20" id="td_8cbd_100" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 1px;font-size: 0px;line-height: 0;">
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <!--[if (gte mso 9)|(IE)]>
                          </td>
                          <td valign="top" >
                            <![endif]-->
                            <table width="auto" align="right" border="0" cellspacing="0" cellpadding="0" class="full-width-center" id="table_8cbd_74" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-spacing: 0;">
                              <tbody>
                                <tr>
                                  <td align="right" id="td_8cbd_101" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;color: #999999;font-weight: normal;text-align: right;word-break: break-word;line-height: 22px;font-size: 14px;">
                                    <div style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;">
                                      <?php echo date('j. ') . date_i18n( 'F' ) . date(' Y'); ?>
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="19" id="td_8cbd_102" style="-webkit-text-size-adjust: none;-ms-text-size-adjust: none;border-collapse: collapse;height: 19px;font-size: 0px;line-height: 0;">
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
