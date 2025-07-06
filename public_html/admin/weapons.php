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
    if (isset($_POST['add_weapon'])) {
        $name = sanitizeInput($_POST['name']);
        $damage = (int)$_POST['damage'];
        $armorPenetration = (int)$_POST['armor_penetration'];
        $dropChance = (float)$_POST['drop_chance'];
        $imagePath = sanitizeInput($_POST['image_path']);
        
        if (empty($name)) {
            $error = 'Nazwa broni jest wymagana.';
        } else {
            try {
                $sql = "INSERT INTO weapons (name, damage, armor_penetration, drop_chance, image_path) 
                        VALUES (?, ?, ?, ?, ?)";
                // ZMIANA: execute() -> query()
                $db->query($sql, [$name, $damage, $armorPenetration, $dropChance, $imagePath]);
                $message = 'Broń została dodana.';
            } catch (Exception $e) {
                $error = 'Błąd dodawania broni: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_weapon'])) {
        $id = (int)$_POST['weapon_id'];
        $name = sanitizeInput($_POST['name']);
        $damage = (int)$_POST['damage'];
        $armorPenetration = (int)$_POST['armor_penetration'];
        $dropChance = (float)$_POST['drop_chance'];
        $imagePath = sanitizeInput($_POST['image_path']);
        
        if (empty($name)) {
            $error = 'Nazwa broni jest wymagana.';
        } else {
            try {
                $sql = "UPDATE weapons SET name = ?, damage = ?, armor_penetration = ?, 
                        drop_chance = ?, image_path = ? WHERE id = ?";
                // ZMIANA: execute() -> query()
                $db->query($sql, [$name, $damage, $armorPenetration, $dropChance, $imagePath, $id]);
                $message = 'Broń została zaktualizowana.';
            } catch (Exception $e) {
                $error = 'Błąd edycji broni: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_weapon'])) {
        $id = (int)$_POST['weapon_id'];
        
        // Sprawdź czy broń nie jest używana
        $inUse = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE equipped_weapon_id = ?", [$id]);
        if ($inUse['count'] > 0) {
            $error = 'Nie można usunąć broni używanej przez postacie.';
        } else {
            try {
                // ZMIANA: execute() -> query()
                $db->query("DELETE FROM weapons WHERE id = ?", [$id]);
                $message = 'Broń została usunięta.';
            } catch (Exception $e) {
                $error = 'Błąd usuwania broni: ' . $e->getMessage();
            }
        }
    }
}

// Pobierz wszystkie bronie
$weapons = $db->fetchAll("
    SELECT w.*, 
           (SELECT COUNT(*) FROM characters WHERE equipped_weapon_id = w.id) as users_count,
           (SELECT COUNT(*) FROM character_weapons WHERE weapon_id = w.id) as inventory_count
    FROM weapons w 
    ORDER BY w.name
");

$smarty->assign('weapons', $weapons);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('weapons.tpl');
?>