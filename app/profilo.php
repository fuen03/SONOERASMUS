<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    header('Location: login.php?error=Devi essere loggato per accedere al profilo');
    exit();
}

$utente_id = $_SESSION['utente_id'];
$success_message = '';
$error_message = '';

try {
    // Si el formulario ha sido enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $cognome = trim($_POST['cognome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password_nuova = $_POST['password'] ?? '';
        
        // Verificar si se quiere eliminar la foto
        $elimina_foto = isset($_POST['elimina_foto']) && $_POST['elimina_foto'] === '1';
        
        // Validaciones básicas
        if (empty($nome) || empty($cognome) || empty($email) || empty($username)) {
            $error_message = "Tutti i campi obbligatori devono essere compilati.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Formato email non valido.";
        } else {
            // Verificar si email o username ya existen (excepto el usuario actual)
            $sql_check = "SELECT COUNT(*) as count FROM Utente WHERE (email = :email OR username = :username) AND id != :id";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([':email' => $email, ':username' => $username, ':id' => $utente_id]);
            $result = $stmt_check->fetch();
            
            if ($result['count'] > 0) {
                $error_message = "Email o username già in uso da un altro utente.";
            } else {
                // Gestión de la foto de perfil
                $foto_path = null;
                $update_foto = false;
                
                if ($elimina_foto) {
                    // Si se quiere eliminar la foto, establecer como null
                    $foto_path = null;
                    $update_foto = true;
                } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    // Si se subió una nueva foto
                    $uploads_dir = '../uploads';
                    if (!is_dir($uploads_dir)) {
                        mkdir($uploads_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = 'user_' . $utente_id . '_' . time() . '.' . $file_extension;
                        $foto_path = 'uploads/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['foto']['tmp_name'], '../' . $foto_path)) {
                            $update_foto = true;
                        } else {
                            $error_message = "Errore nel caricamento dell'immagine.";
                        }
                    } else {
                        $error_message = "Formato immagine non valido. Usa JPG, PNG o GIF.";
                    }
                }
                
                if (empty($error_message)) {
                    // Preparar query de actualización
                    $sql = "UPDATE Utente SET nome=:nome, cognome=:cognome, email=:email, username=:username";
                    $params = [
                        ':nome' => $nome,
                        ':cognome' => $cognome,
                        ':email' => $email,
                        ':username' => $username,
                        ':id' => $utente_id
                    ];
                    
                    // Agregar password si se proporcionó
                    if (!empty($password_nuova)) {
                        if (strlen($password_nuova) < 8) {
                            $error_message = "La password deve contenere almeno 8 caratteri.";
                        } else {
                            $sql .= ", password=:password";
                            $params[':password'] = password_hash($password_nuova, PASSWORD_DEFAULT);
                        }
                    }
                    
                    // Agregar foto si se actualizó
                    if ($update_foto) {
                        $sql .= ", foto=:foto";
                        $params[':foto'] = $foto_path;
                    }
                    
                    $sql .= " WHERE id=:id";
                    
                    if (empty($error_message)) {
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        // Actualizar variables de sesión
                        $_SESSION['utente_nome'] = $nome;
                        $_SESSION['utente_cognome'] = $cognome;
                        $_SESSION['utente_email'] = $email;
                        $_SESSION['utente'] = $username;
                        $_SESSION['utente_username'] = $username;
                        
                        if ($update_foto) {
                            $_SESSION['utente_foto'] = $foto_path;
                        }
                        
                        $success_message = "Profilo aggiornato con successo!";
                    }
                }
            }
        }
    }
    
    // Cargar datos actuales del usuario
    $stmt = $pdo->prepare("SELECT * FROM Utente WHERE id = :id");
    $stmt->execute([':id' => $utente_id]);
    $utente = $stmt->fetch();
    
    if (!$utente) {
        header('Location: login.php?error=Usuario no encontrado');
        exit();
    }
    
    // Cargar eventos a los que está inscrito
    $sql_eventi = "SELECT e.id, e.titolo, e.descrizione, e.data_evento, e.luogo, u.nome AS universita
                   FROM Evento e
                   JOIN Partecipazione p ON e.id = p.evento_id
                   LEFT JOIN Universita u ON e.universita_id = u.id
                   WHERE p.utente_id = :id
                   ORDER BY e.data_evento ASC";
    $stmt_eventi = $pdo->prepare($sql_eventi);
    $stmt_eventi->execute([':id' => $utente_id]);
    $eventi_iscritti = $stmt_eventi->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error en perfil: " . $e->getMessage());
    $error_message = "Errore nel caricamento del profilo.";
}

// Gestire messaggi de eliminación de inscripción
$status = $_GET['status'] ?? '';
if ($status === 'cancellato') {
    $success_message = "Iscrizione cancellata con successo!";
} elseif ($status === 'errore') {
    $error_message = $_GET['message'] ?? "Si è verificato un errore.";
}

// La función getDefaultAvatar está ahora en config.php
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il mio profilo - SonoErasmus+</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="profilo-container">
        <a href="../index.php" class="back-link">← Torna alla home</a>
        
        <h1>Il mio profilo</h1>
        
        <!-- Messaggi -->
        <?php if ($success_message): ?>
            <div class="success-message">✅ <?= h($success_message) ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message">❌ <?= h($error_message) ?></div>
        <?php endif; ?>
        
        <!-- Formulario del perfil -->
        <div class="profilo-card">
            <h2 class="section-title">I tuoi dati</h2>
            
            <div class="current-photo-section">
                <?php if (!empty($utente['foto'])): ?>
                    <div class="current-photo">
                        <img src="../<?= h($utente['foto']) ?>" alt="Foto profilo">
                        <p class="photo-label">Foto attuale</p>
                    </div>
                <?php else: ?>
                    <div class="current-photo">
                        <img src="<?= getDefaultAvatar($utente['nome'] . ' ' . $utente['cognome'], 120) ?>" alt="Foto profilo predefinita">
                        <p class="photo-label">Foto predefinita</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-field">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" value="<?= h($utente['nome']) ?>" required>
                    </div>
                    <div class="form-field">
                        <label for="cognome">Cognome *</label>
                        <input type="text" id="cognome" name="cognome" value="<?= h($utente['cognome']) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= h($utente['email']) ?>" required>
                    </div>
                    <div class="form-field">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" value="<?= h($utente['username']) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="password">Nuova password (opzionale)</label>
                        <input type="password" id="password" name="password" placeholder="Lascia vuoto per non cambiare">
                    </div>
                    <div class="form-field">
                        <label for="foto">Cambia foto profilo</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <small class="form-help">Formati supportati: JPG, PNG, GIF. Max 5MB</small>
                    </div>
                </div>
                
                <!-- Opzioni per la foto -->
                <div class="photo-options">
                    <?php if (!empty($utente['foto'])): ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="elimina_foto" value="1" id="eliminaFoto">
                            <span class="checkmark"></span>
                            Elimina foto attuale e usa quella predefinita
                        </label>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-primary">💾 Salva modifiche</button>
            </form>
        </div>
        
        <!-- Eventi iscritti -->
        <div class="profilo-card">
            <h2 class="section-title">Eventi a cui sei iscritto</h2>
            
            <?php if (empty($eventi_iscritti)): ?>
                <div class="no-eventi">
                    <p>Non sei ancora iscritto a nessun evento.</p>
                    <a href="eventi.php" class="btn-primary">Scopri gli eventi disponibili</a>
                </div>
            <?php else: ?>
                <ul class="eventi-list">
                    <?php foreach ($eventi_iscritti as $evento): ?>
                        <li class="evento-item">
                            <div class="evento-title"><?= h($evento['titolo']) ?></div>
                            <div class="evento-details">
                                <?= h($evento['descrizione']) ?><br>
                                📍 <?= h($evento['luogo']) ?> — 
                                📅 <?= date('d/m/Y H:i', strtotime($evento['data_evento'])) ?>
                                <?php if ($evento['universita']): ?>
                                    <br>🎓 Università: <?= h($evento['universita']) ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($evento['id'])): ?>
                                <form action="elimina_iscrizione.php" method="POST" class="elimina-form" 
                                      onsubmit="return confirm('Sei sicuro di voler cancellare l\'iscrizione a questo evento?')">
                                    <input type="hidden" name="evento_id" value="<?= h($evento['id']) ?>">
                                    <button type="submit" class="btn-elimina">❌ Cancella iscrizione</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/user-system.js"></script>
    <script>
        // Script para manejar la previsualización de la foto y la opción de eliminar
        document.addEventListener('DOMContentLoaded', function() {
            const fotoInput = document.getElementById('foto');
            const eliminaFotoCheckbox = document.getElementById('eliminaFoto');
            
            // Cuando se selecciona una nueva foto, desmarcar "eliminar"
            if (fotoInput && eliminaFotoCheckbox) {
                fotoInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        eliminaFotoCheckbox.checked = false;
                    }
                });
                
                // Cuando se marca "eliminar", limpiar input de foto
                eliminaFotoCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        fotoInput.value = '';
                    }
                });
            }
        });
</body>
</html>
