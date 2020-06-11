<?php

namespace GiftTest;

if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Cleanup all options on plugin uninstall
 */

$settingsPage = new SettingsPage();
$success = true;
foreach ( $settingsPage->getQuestionaireList() as $questionaire ) {
	$success = $success && $questionaire->delete();
}
return $success;
