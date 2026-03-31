<?php
// --- 🧠 BACKEND: SQL DATABASE LOGIC ---
$dbFile = 'diu_database.sqlite';
$guests = []; 

try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS alumni (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT,
        age INTEGER,
        category TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
        $stmt = $db->prepare("INSERT INTO alumni (name, phone, age, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['age'], $_POST['group']]);
        header("Location: index.php");
        exit;
    }

    if (isset($_GET['delete'])) {
        $stmt = $db->prepare("DELETE FROM alumni WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: index.php");
        exit;
    }

    $query = $db->query("SELECT * FROM alumni ORDER BY created_at DESC");
    $guests = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

$vips = count(array_filter($guests, fn($g) => $g['category'] === 'VIP'));
$faculty = count(array_filter($guests, fn($g) => $g['category'] === 'Faculty'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DIU Alumni Portal</title>
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; color: #334155; }
        .header { background: #004a99; color: white; padding: 60px 20px; text-align: center; border-bottom: 8px solid #ffd700; }
        .container { max-width: 850px; margin: -40px auto 60px; padding: 0 20px; }
        .stats-grid { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { flex: 1; background: white; padding: 25px; border-radius: 15px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #004a99; }
        .card { background: white; padding: 35px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        input, select { padding: 14px; margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 8px; width: 100%; box-sizing: border-box; font-size: 16px; }
        button { background: #004a99; color: white; border: none; padding: 18px; border-radius: 10px; width: 100%; font-weight: bold; cursor: pointer; font-size: 18px; transition: 0.3s; }
        button:hover { background: #003366; }
        .guest-row { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .delete-link { color: #ef4444; text-decoration: none; font-size: 0.9rem; border: 1px solid #fee2e2; padding: 5px 10px; border-radius: 6px; }
        
        .map-link { color: #ffd700; text-decoration: none; font-weight: bold; font-size: 0.9rem; border: 2px solid #ffd700; padding: 12px 30px; border-radius: 30px; transition: 0.3s; display: inline-block; margin-top: 15px; }
        .map-link:hover { background: #ffd700; color: #004a99; }
    </style>
</head>
<body>

    <div class="header">
        <h1 style="font-size: 2.5rem; margin: 0;">🎓 DIU Alumni Get Together</h1>
        <p style="font-size: 1.2rem; margin: 10px 0;">Permanent Campus | Savar</p>
        
        <a href="https://maps.google.com/?q=Daffodil+International+University+Permanent+Campus" 
           target="_blank" 
           rel="noreferrer" 
           class="map-link">📍 Open Campus Map</a>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <small style="font-weight: bold; color: #64748b;">VIP / Faculty</small>
                <h2 style="color: #004a99; margin: 5px 0;"><?= $vips ?> / <?= $faculty ?></h2>
            </div>
            <div class="stat-card">
                <small style="font-weight: bold; color: #64748b;">Total Registered</small>
                <h2 style="color: #004a99; margin: 5px 0;"><?= count($guests) ?></h2>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-top: 0; color: #1e293b;">Alumni RSVP</h2>
            <form method="POST">
                <input name="name" placeholder="Full Name" required>
                <div style="display: flex; gap: 10px;">
                    <input name="phone" placeholder="Phone Number">
                    <input name="age" type="number" placeholder="Age">
                </div>
                <select name="group">
                    <option>General</option>
                    <option>VIP</option>
                    <option>Faculty</option>
                </select>
                <button type="submit">Submit Registration</button>
            </form>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 35px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h2 style="margin: 0; font-size: 1.2rem;">Confirmed Attendee Directory</h2>
            </div>
            <?php if (empty($guests)): ?>
                <p style="padding: 20px; text-align: center; color: #94a3b8;">No attendees registered yet.</p>
            <?php else: ?>
                <?php foreach($guests as $g): ?>
                    <div class="guest-row">
                        <div style="padding-left: 20px;">
                            <strong><?= htmlspecialchars($g['name']) ?></strong><br>
                            <small style="color: #64748b;"><?= htmlspecialchars($g['phone']) ?> • <?= $g['category'] ?></small>
                        </div>
                        <div style="padding-right: 20px;">
                            <a href="?delete=<?= $g['id'] ?>" class="delete-link" onclick="return confirm('Remove guest?')">Remove</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>