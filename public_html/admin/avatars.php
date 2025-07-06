<?php
session_start();
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
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
    if (isset($_POST['add_avatar'])) {
        $imagePath = sanitizeInput($_POST['image_path']);
        $gender = sanitizeInput($_POST['gender']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($imagePath) || !in_array($gender, ['male', 'female', 'unisex'])) {
            $error = 'Ścieżka obrazka i płeć są wymagane.';
        } else {
            try {
                $sql = "INSERT INTO avatar_images (image_path, gender, is_active) VALUES (?, ?, ?)";
                $db->query($sql, [$imagePath, $gender, $isActive]);
                $message = 'Avatar został dodany.';
            } catch (Exception $e) {
                $error = 'Błąd dodawania avatara: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_avatar'])) {
        $id = (int)$_POST['avatar_id'];
        $imagePath = sanitizeInput($_POST['image_path']);
        $gender = sanitizeInput($_POST['gender']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($imagePath) || !in_array($gender, ['male', 'female', 'unisex'])) {
            $error = 'Ścieżka obrazka i płeć są wymagane.';
        } else {
            try {
                $sql = "UPDATE avatar_images SET image_path = ?, gender = ?, is_active = ? WHERE id = ?";
                $db->query($sql, [$imagePath, $gender, $isActive, $id]);
                $message = 'Avatar został zaktualizowany.';
            } catch (Exception $e) {
                $error = 'Błąd edycji avatara: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_avatar'])) {
        $id = (int)$_POST['avatar_id'];
        
        // Sprawdź czy avatar nie jest używany
        $inUse = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE avatar_image = (SELECT image_path FROM avatar_images WHERE id = ?)", [$id]);
        if ($inUse['count'] > 0) {
            $error = 'Nie można usunąć avatara używanego przez postacie.';
        } else {
            try {
                $db->query("DELETE FROM avatar_images WHERE id = ?", [$id]);
                $message = 'Avatar został usunięty.';
            } catch (Exception $e) {
                $error = 'Błąd usuwania avatara: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['toggle_avatar'])) {
        $id = (int)$_POST['avatar_id'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            $db->query("UPDATE avatar_images SET is_active = ? WHERE id = ?", [$isActive, $id]);
            $message = 'Status avatara został zmieniony.';
        } catch (Exception $e) {
            $error = 'Błąd zmiany statusu avatara: ' . $e->getMessage();
        }
    } elseif (isset($_POST['bulk_add_avatars'])) {
        $avatarsData = sanitizeInput($_POST['avatars_data']);
        $defaultGender = sanitizeInput($_POST['default_gender']);
        
        if (!empty($avatarsData)) {
            $lines = explode("\n", trim($avatarsData));
            $added = 0;
            $errors = 0;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Format: /path/to/avatar.png|gender lub samo /path/to/avatar.png
                $parts = explode('|', $line);
                $imagePath = trim($parts[0]);
                $gender = isset($parts[1]) ? trim($parts[1]) : $defaultGender;
                
                if (!in_array($gender, ['male', 'female', 'unisex'])) {
                    $gender = $defaultGender;
                }
                
                try {
                    $sql = "INSERT INTO avatar_images (image_path, gender, is_active) VALUES (?, ?, 1)";
                    $db->query($sql, [$imagePath, $gender]);
                    $added++;
                } catch (Exception $e) {
                    $errors++;
                }
            }
            
            $message = "Dodano {$added} avatarów. Błędów: {$errors}";
        }
    }
}

// Pobierz avatary z statystykami użycia
$avatars = $db->fetchAll("
    SELECT a.*, 
           (SELECT COUNT(*) FROM characters WHERE avatar_image = a.image_path) as usage_count
    FROM avatar_images a 
    ORDER BY a.gender, a.image_path
");

// Statystyki avatarów
$avatarStats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male,
        SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female,
        SUM(CASE WHEN gender = 'unisex' THEN 1 ELSE 0 END) as unisex
    FROM avatar_images
");

$smarty->assign('avatars', $avatars);
$smarty->assign('avatar_stats', $avatarStats);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('avatars.tpl');
?>