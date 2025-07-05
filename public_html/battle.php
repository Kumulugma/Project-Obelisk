<?php
session_start();
require_once '../rpg-game/includes/config.php';
require_once '../rpg-game/includes/database.php';
require_once '../rpg-game/includes/Battle.php';
require_once '../rpg-game/includes/Character.php';
require_once '../rpg-game/includes/functions.php';
require_once '../rpg-game/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR);
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$battleId = intval($_GET['id'] ?? 0);
if (!$battleId) {
    header('Location: /');
    exit;
}

$battle = new Battle();
$battleData = $battle->getBattleDetails($battleId);

if (!$battleData) {
    header('Location: /');
    exit;
}

$weaponInfo = null;
$traitInfo = null;

if ($battleData['weapon_dropped']) {
    $db = Database::getInstance();
    $weaponInfo = $db->fetchOne("SELECT * FROM weapons WHERE id = ?", [$battleData['weapon_dropped']]);
}

if ($battleData['trait_dropped']) {
    $db = Database::getInstance();
    $traitInfo = $db->fetchOne("SELECT * FROM traits WHERE id = ?", [$battleData['trait_dropped']]);
}

$smarty->assign('battle', $battleData);
$smarty->assign('weapon_reward', $weaponInfo);
$smarty->assign('trait_reward', $traitInfo);
$smarty->assign('is_mobile', isMobile());
$smarty->assign('site_url', SITE_URL);

$smarty->display('battle.tpl');
?>