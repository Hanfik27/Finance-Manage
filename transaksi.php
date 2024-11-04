<?php
session_start();
include "conn.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function addTransaction($user_id, $type, $amount, $description, $date) {
    global $conn;
    $sql = "INSERT INTO transactions (user_id, type, amount, description, date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $user_id, $type, $amount, $description, $date);
    return $stmt->execute();
}

function getTransactions($user_id) {
    global $conn;
    $sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotals($user_id) {
    global $conn;
    $sql = "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM transactions WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Error in getTotals: " . $conn->error);
        return ['total_income' => 0, 'total_expense' => 0];
    }
    return $result->fetch_assoc();
}

function deleteTransaction($transaction_id, $user_id) {
    global $conn;
    $sql = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $transaction_id, $user_id);
    return $stmt->execute();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $transaction_id = $_POST['transaction_id'];
        if (deleteTransaction($transaction_id, $_SESSION['user_id'])) {
            $message = "Transaksi berhasil dihapus.";
        } else {
            $error = "Terjadi kesalahan saat menghapus transaksi. Silakan coba lagi.";
        }
        header("Location: transaksi.php"); // Redirect setelah menghapus transaksi
        exit();
    } else {
        $type = $_POST["type"];
        $amount = $_POST["amount"];
        $description = $_POST["description"];
        $date = $_POST["date"];
        
        if (addTransaction($_SESSION['user_id'], $type, $amount, $description, $date)) {
            $message = "Transaksi berhasil ditambahkan.";
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
        header("Location: transaksi.php"); // Redirect setelah menambah transaksi
        exit();
    }
}


$transactions = getTransactions($_SESSION['user_id']);
$totals = getTotals($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencatat Keuangan</title>
    <link rel="stylesheet" href="./style/transaksi.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Finance Manager Monthly</h1>
            <div class="user-info">
                <img class="pp" src="./src/pp.jpg" alt="profile">
                <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Form input transaksi -->
        <form method="POST" class="transaction-form">
            <h2>Form Input Transaction</h2>
            <div class="form-group">
                <label for="type">Tipe Transaksi</label>
                <select name="type" id="type">
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Jumlah</label>
                <input type="number" name="amount" id="amount" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <input type="text" name="description" id="description" required>
            </div>
            <div class="form-group">
                <label for="date">Tanggal</label>
                <input type="date" name="date" id="date" required>
            </div>
            <button type="submit" class="submit-btn">Tambah Transaksi</button>
        </form>

        <!-- Ringkasan -->
        <div class="summary">
            <h2>Ringkasan</h2>
            <p>Total Pemasukan: Rp <?php echo number_format($totals['total_income'], 0, ',', '.'); ?></p>
            <p>Total Pengeluaran: Rp <?php echo number_format($totals['total_expense'], 0, ',', '.'); ?></p>
            <p>Saldo: Rp <?php echo number_format($totals['total_income'] - $totals['total_expense'], 0, ',', '.'); ?></p>
        </div>

        <!-- Daftar Transaksi -->
        <div class="transaction-list">
            <h2>Daftar Transaksi</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                        <td><?php echo $transaction['type'] == 'income' ? 'Pemasukan' : 'Pengeluaran'; ?></td>
                        <td>Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                <button type="submit" class="delete-btn">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>    
</body>
</html>