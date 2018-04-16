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
  'theme_title_by_dates'              => 'Udalosti na tento týždeň',
  'theme_title_latest'                => 'Nové udalosti',
  'theme_max_events_by_dates'         => 5,
  'theme_start_date'                  => '',
  'theme_end_date'                    => '',
  'theme_max_events_latest'           => 5,
  'theme_orderby'                     => 'category',
  'theme_categories'                  => array(),
  'theme_tags'                        => array(),
  'theme_terms_hidden'                => 1,
);

$aTermArgs = array(
  'hide_empty'  => false,
  'orderby'     => 'term_id',
  'fields'      => 'id=>name',
);
$strAi1ecCategoryTaxonomy = 'events_categories';
$strAi1ecTagTaxonomy      = 'events_tags';
$aTags                    = get_terms( $strAi1ecTagTaxonomy, $aTermArgs );
$aCategories              = get_terms( $strAi1ecCategoryTaxonomy, $aTermArgs );
$aCategorySlugs           = get_terms( $strAi1ecCategoryTaxonomy, array_merge($aTermArgs, array( 'fields' => 'id=>slug' ) ));

$strDefaultCategoryOrder_notValidated = "ine,workshopy,party,-";
$aDefaultCategoryOrder_notValidated = explode( ",", $strDefaultCategoryOrder_notValidated );
$aDefaultCategoryOrder = array();
foreach ($aDefaultCategoryOrder_notValidated as $slug) {
  if (in_array($slug, $aCategorySlugs) || $slug === '-') {
    $aDefaultCategoryOrder[] = $slug;
  }
}
$strDefaultCategoryOrder = implode( ',', $aDefaultCategoryOrder);
$theme_defaults['theme_category_order'] = $strDefaultCategoryOrder;
$aCategoriesBySlugs = array();

foreach ($aTags as $id => $name) {
  $theme_defaults['theme_tags'][] = $id;
}
$aCategoryNamesBySlugs = array();
foreach ($aCategories as $id => $name) {
  $theme_defaults['theme_categories'][] = $id;
  $aCategoryNamesBySlugs[$aCategorySlugs[$id]] = $name;
}
$aCategoryNamesBySlugs["-"] = "(nezaradené)";

$iTermsSet = $controls->get_value("theme_terms_hidden");
$bTermsSet = isset($iTermsSet);
$aCategoriesBeforeMerge = $controls->get_value("theme_categories");
$aTagsBeforeMerge = $controls->get_value("theme_tags");

if ($bTermsSet) {
  if (!isset($aCategoriesBeforeMerge) || empty($aCategoriesBeforeMerge)) {
    $theme_defaults['theme_categories'] = array();
  }
  if (!isset($aTagsBeforeMerge) || empty($aTagsBeforeMerge)) {
    $theme_defaults['theme_tags'] = array();
  }
}

// Mandatory!
$controls->merge_defaults($theme_defaults);

$strCategorySlugsOrder = $controls->get_value("theme_category_order");
$aCategorySlugsOrder = explode(',', $strCategorySlugsOrder);
$aOrderedCategories = array();
foreach ($aCategorySlugsOrder as $slug) {
  $aOrderedCategories[$slug] = $aCategoryNamesBySlugs[$slug];
}
$aOrderedCategories += $aCategoryNamesBySlugs;
?>

<table class="form-table">
    <tr valign="top">
        <th>Nadpis časti generovanej na základe rozpätia dátumov</th>
        <td>
            <?php $controls->text('theme_title_by_dates', 20); ?>
        </td>
    </tr>
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
        <th>Nadpis časti s najnovšími udalosťami</th>
        <td>
            <?php $controls->text('theme_title_latest', 20); ?>
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
    <tr valign="top" id="theme_category_order-tr">
        <th>Poradie kategórií</th>
        <td>
            <?php $controls->hidden('theme_category_order'); ?>
            <ul id="theme_category_order-ul">
              <?php
                foreach ($aOrderedCategories as $slug => $name) {
                  echo "<li id='theme_category_order-li-{$slug}' class='newsletter-checkboxes-item theme_category_order-li' title='{$name}'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span> {$name}</li>".PHP_EOL;
                }
              ?>
            </ul>
        </td>
    </tr>
    <tr valign="top">
        <th>Použiť udalosti v týchto kategóriách</th>
        <td class="term_checkboxes">
            <?php
              $controls->hidden('theme_terms_hidden');
              $controls->checkboxes_group('theme_categories', $aCategories);
            ?>
        </td>
    </tr>
    <tr valign="top">
        <th>Použiť udalosti s týmito tagmi</th>
        <td class="term_checkboxes">
            <?php $controls->checkboxes_group('theme_tags', $aTags); ?>
        </td>
    </tr>
</table>

<script type="text/javascript">
  function srdNlOptionsShowHideCategoryOrder() {
    var orderby = jQuery("#options-theme_orderby").val()
    var categoryOrderTR = jQuery("#theme_category_order-tr")
    if (orderby === 'category') {
      categoryOrderTR.show()
    } else {
      categoryOrderTR.hide()
    }
  }
  
  jQuery(document).ready(function() {
    srdNlOptionsShowHideCategoryOrder()
    jQuery("#options-theme_orderby").change(function () { srdNlOptionsShowHideCategoryOrder() });
    jQuery("#theme_category_order-ul").sortable({
      stop: function( event, ui ) {
        var aSortedSlugs = $( "#theme_category_order-ul" ).sortable( "toArray" )
        var strSortedSlugs = aSortedSlugs.join(",").replace(/theme_category_order-li-/g, "")
        jQuery("input[name*=theme_category_order]").val(strSortedSlugs)
      },
      cursor: "move",
    })
    jQuery("#theme_category_order-ul").disableSelection()
    jQuery(".term_checkboxes .newsletter-checkboxes-item label").each(function() {
      $this = jQuery(this)
      $this.attr("title", $this.text())
    })
    jQuery(".theme_category_order-li").css("cursor", "pointer")
  })
</script>
