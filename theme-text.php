<?php
if (!defined('ABSPATH'))
    exit;
?>
Tento email vyžaduje moderný prehliadač emailov. Môžete si ho však pozrieť tu:

{email_url}.

Ďakujeme,

<?php echo wp_specialchars_decode(get_option('blogname'), ENT_QUOTES); ?>

Pre zmenu odberu, kliknite sem:
{profile_url}.