<?php
/*
 * This is a pre packaged theme options page. Every option name
 * must start with "theme_" so Newsletter can distinguish them from other
 * options that are specific to the object using the theme.
 *
 * An array of theme default options should always be present and that default options
 * should be merged with the current complete set of options as shown below.
 *
 * Every theme can define its own set of options, the will be used in the theme.php
 * file while composing the email body. Newsletter knows nothing about theme options
 * (other than saving them) and does not use or relies on any of them.
 *
 * For multilanguage purpose you can actually check the constants "WP_LANG", until
 * a decent system will be implemented.
 */

if (!defined('ABSPATH'))
    exit;

$iDayOfWeekFrom = 1; // Monday
$iDefaultStartTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + $iDayOfWeekFrom);
$strDefaultStartDate = date( 'j.n.Y', $iDefaultStartTimestamp );
$iDefaultEndTimestamp = mktime(0, 0, 0, date("n"), date("j") - date("N") + $iDayOfWeekFrom + 7) - 1;
$strDefaultEndDate = date( 'j.n.Y', $iDefaultEndTimestamp );

$iLatestEventsAddedDayOfWeekFrom = 1; // Monday
$iLatestEventsDefaultAddedStartTimestamp = mktime(12, 0, 0, date("n"), date("j") - date("N") - 7 + $iLatestEventsAddedDayOfWeekFrom);
$strLatestEventsDefaultAddedStartDate = date( 'j.n.Y G:i', $iLatestEventsDefaultAddedStartTimestamp );

$theme_defaults = array(
  'theme_max_events_by_dates'         => 5,
  'theme_start_date'                  => '',
  'theme_end_date'                    => '',
  'theme_max_events_latest'           => 5,
  'theme_orderby'                     => 'category',
);

// Mandatory!
$controls->merge_defaults($theme_defaults);
?>

<table class="form-table">
    <tr valign="top">
        <th>Maximálny počet udalostí v časti generovanej na základe rozpätia dátumov</th>
        <td>
            <?php $controls->text('theme_max_events_by_dates', 1); ?> (ak sa nevyplní, použije sa 10)
        </td>
    </tr>
    <tr valign="top">
        <th>Začiatočný dátum časti generovanej na základe rozpätia dátumov</th>
        <td>
            <?php $controls->text('theme_start_date', 10); ?> (v tvare d.m.rrrr; ak sa nevyplní, použije sa pondelok v aktuálnom týždni: <?php echo $strDefaultStartDate; ?>)
        </td>
    </tr>
    <tr valign="top">
        <th>Koncový dátum časti generovanej na základe rozpätia dátumov</th>
        <td>
            <?php $controls->text('theme_end_date', 10); ?> (v tvare d.m.rrrr; ak sa nevyplní, použije sa nedeľa v aktuálnom týždni: <?php echo $strDefaultEndDate; ?>)
        </td>
    </tr>
    <tr valign="top">
        <th>Minimálny (najskorší) dátum a čas pridania najnovších udalostí</th>
        <td>
            <?php $controls->text('theme_start_date_added_latest', 10); ?> (v tvare d.m.rrrr h:mm; ak sa nevyplní, použije sa pondelok 12:00 predošlého týždňa: <?php echo $strLatestEventsDefaultAddedStartDate; ?>)
        </td>
    </tr>
    <tr valign="top">
        <th>Maximálny počet najnovších udalostí</th>
        <td>
            <?php $controls->text('theme_max_events_latest', 1); ?> (ak sa nevyplní, použije sa 10)
        </td>
    </tr>
    <tr valign="top">
        <th>Zoradiť udalosti podľa</th>
        <td>
            <?php $controls->select('theme_orderby', array('category' => 'Kategórie', 'date' => 'Dátumu')); ?>
        </td>
    </tr>
</table>
