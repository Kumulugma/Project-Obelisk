<?php
session_start();
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
require_once '../../rpg-game/includes/Character.php';
require_once '../../rpg-game/includes/Battle.php';
require_once '../../rpg-game/includes/functions.php';
require_once '../../rpg-game/vendor/autoload.php';

// Sprawdź autoryzację
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR . 'admin/');
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$db = Database::getInstance();
$message = '';
$error = '';

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_trait'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $type = sanitizeInput($_POST['type']);
        $effectType = sanitizeInput($_POST['effect_type']);
        $effectTarget = sanitizeInput($_POST['effect_target']);
        $effectValue = (int)$_POST['effect_value'];
        $effectDuration = (int)$_POST['effect_duration'];
        $triggerChance = (float)$_POST['trigger_chance'];
        $dropChance = (float)$_POST['drop_chance'];
        $imagePath = sanitizeInput($_POST['image_path']);
        $avatarModifier = sanitizeInput($_POST['avatar_modifier']);
        
        if (empty($name) || empty($type)) {
            $error = 'Nazwa i typ traitu są wymagane.';
        } else {
            try {
                $sql = "INSERT INTO traits (name, description, type, effect_type, effect_target, 
                        effect_value, effect_duration, trigger_chance, drop_chance, image_path, 
                        avatar_modifier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $db->execute($sql, [$name, $description, $type, $effectType, $effectTarget, 
                                  $effectValue, $effectDuration, $triggerChance, $dropChance, 
                                  $imagePath, $avatarModifier]);
                $message = 'Trait został dodany.';
            } catch (Exception $e) {
                $error = 'Błąd dodawania traita: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_trait'])) {
        $id = (int)$_POST['trait_id'];
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $type = sanitizeInput($_POST['type']);
        $effectType = sanitizeInput($_POST['effect_type']);
        $effectTarget = sanitizeInput($_POST['effect_target']);
        $effectValue = (int)$_POST['effect_value'];
        $effectDuration = (int)$_POST['effect_duration'];
        $triggerChance = (float)$_POST['trigger_chance'];
        $dropChance = (float)$_POST['drop_chance'];
        $imagePath = sanitizeInput($_POST['image_path']);
        $avatarModifier = sanitizeInput($_POST['avatar_modifier']);
        
        if (empty($name) || empty($type)) {
            $error = 'Nazwa i typ traita są wymagane.';
        } else {
            try {
                $sql = "UPDATE traits SET name = ?, description = ?, type = ?, effect_type = ?, 
                        effect_target = ?, effect_value = ?, effect_duration = ?, trigger_chance = ?, 
                        drop_chance = ?, image_path = ?, avatar_modifier = ? WHERE id = ?";
                $db->execute($sql, [$name, $description, $type, $effectType, $effectTarget, 
                                  $effectValue, $effectDuration, $triggerChance, $dropChance, 
                                  $imagePath, $avatarModifier, $id]);
                $message = 'Trait został zaktualizowany.';
            } catch (Exception $e) {
                $error = 'Błąd edycji traita: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_trait'])) {
        $id = (int)$_POST['trait_id'];
        
        // Sprawdź czy trait nie jest używany
        $inUse = $db->fetchOne("SELECT COUNT(*) as count FROM character_traits WHERE trait_id = ?", [$id]);
        if ($inUse['count'] > 0) {
            $error = 'Nie można usunąć traita używanego przez postacie.';
        } else {
            try {
                $db->execute("DELETE FROM traits WHERE id = ?", [$id]);
                $message = 'Trait został usunięty.';
            } catch (Exception $e) {
                $error = 'Błąd usuwania traita: ' . $e->getMessage();
            }
        }
    }
}

// Pobierz wszystkie traity
$traits = $db->fetchAll("
    SELECT t.*, 
           (SELECT COUNT(*) FROM character_traits WHERE trait_id = t.id) as users_count
    FROM traits t 
    ORDER BY t.type, t.name
");

$smarty->assign('traits', $traits);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('traits.tpl');
?>