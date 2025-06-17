<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            $pdo = getConnection();
            
            if ($_POST['action'] == 'add') {
                $stmt = $pdo->prepare("INSERT INTO payments (player_id, amount, payment_date, payment_method, description, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['player_id'],
                    $_POST['amount'],
                    $_POST['payment_date'],
                    $_POST['payment_method'],
                    $_POST['description'],
                    $_POST['status']
                ]);
                $message = 'Payment record added successfully!';
            } elseif ($_POST['action'] == 'edit') {
                $stmt = $pdo->prepare("UPDATE payments SET player_id = ?, amount = ?, payment_date = ?, payment_method = ?, description = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['player_id'],
                    $_POST['amount'],
                    $_POST['payment_date'],
                    $_POST['payment_method'],
                    $_POST['description'],
                    $_POST['status'],
                    $_POST['payment_id']
                ]);
                $message = 'Payment record updated successfully!';
            } elseif ($_POST['action'] == 'delete' && isset($_POST['payment_id'])) {
                $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
                $stmt->execute([$_POST['payment_id']]);
                $message = 'Payment record deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all payments
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT p.*, pl.name as player_name FROM payments p 
                         LEFT JOIN players pl ON p.player_id = pl.id 
                         ORDER BY p.payment_date DESC");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get players for dropdown
    $stmt = $pdo->query("SELECT id, name FROM players ORDER BY name");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching payments: ' . $e->getMessage();
}

// Get payment to edit
$edit_payment = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_payment = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching payment: ' . $e->getMessage();
    }
}

// Calculate totals
$total_paid = 0;
$total_pending = 0;
foreach ($payments as $payment) {
    if ($payment['status'] == 'paid') {
        $total_paid += $payment['amount'];
    } elseif ($payment['status'] == 'pending') {
        $total_pending += $payment['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Elite Football Academy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .payment-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .payment-stat {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .payment-stat h4 {
            margin: 0 0 0.5rem 0;
            color: var(--dark-text);
        }
        .payment-stat .amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--bronze-brown);
        }
        .payment-stat.paid .amount {
            color: #28a745;
        }
        .payment-stat.pending .amount {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <h1>Elite Football Academy - Admin Panel</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php" style="color: var(--primary-gold);">Logout</a></p>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="players.php">Players</a></li>
            <li><a href="schedule.php">Schedule</a></li>
            <li><a href="gallery.php">Gallery</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="payments.php" class="active">Payments</a></li>
            <li><a href="news.php">News</a></li>
            <li><a href="media.php">Media</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="signup.php">Create Admin</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Manage Payments</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Payment Summary -->
        <div class="payment-summary">
            <div class="payment-stat paid">
                <h4>Total Paid</h4>
                <div class="amount">$<?php echo number_format($total_paid, 2); ?></div>
            </div>
            <div class="payment-stat pending">
                <h4>Pending Payments</h4>
                <div class="amount">$<?php echo number_format($total_pending, 2); ?></div>
            </div>
            <div class="payment-stat">
                <h4>Total Records</h4>
                <div class="amount"><?php echo count($payments); ?></div>
            </div>
        </div>

        <!-- Add/Edit Payment Form -->
        <div class="admin-card">
            <h3><?php echo $edit_payment ? 'Edit Payment Record' : 'Add New Payment Record'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_payment ? 'edit' : 'add'; ?>">
                <?php if ($edit_payment): ?>
                    <input type="hidden" name="payment_id" value="<?php echo $edit_payment['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="player_id">Player</label>
                        <select id="player_id" name="player_id" required>
                            <option value="">Select Player</option>
                            <?php foreach ($players as $player): ?>
                                <option value="<?php echo $player['id']; ?>" <?php echo $edit_payment && $edit_payment['player_id'] == $player['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($player['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount ($)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?php echo $edit_payment ? $edit_payment['amount'] : ''; ?>" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        <input type="date" id="payment_date" name="payment_date" value="<?php echo $edit_payment ? $edit_payment['payment_date'] : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash" <?php echo $edit_payment && $edit_payment['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="credit_card" <?php echo $edit_payment && $edit_payment['payment_method'] == 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                            <option value="bank_transfer" <?php echo $edit_payment && $edit_payment['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                            <option value="check" <?php echo $edit_payment && $edit_payment['payment_method'] == 'check' ? 'selected' : ''; ?>>Check</option>
                            <option value="paypal" <?php echo $edit_payment && $edit_payment['payment_method'] == 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                            <option value="other" <?php echo $edit_payment && $edit_payment['payment_method'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="paid" <?php echo $edit_payment && $edit_payment['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="pending" <?php echo $edit_payment && $edit_payment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="failed" <?php echo $edit_payment && $edit_payment['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $edit_payment && $edit_payment['status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" value="<?php echo $edit_payment ? htmlspecialchars($edit_payment['description']) : ''; ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><?php echo $edit_payment ? 'Update Payment' : 'Add Payment'; ?></button>
                    <?php if ($edit_payment): ?>
                        <a href="payments.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Payments List -->
        <div class="admin-card">
            <h3>Payment Records</h3>
            <?php if (empty($payments)): ?>
                <p>No payment records found.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['player_name']); ?></td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                <td>
                                    <span class="btn-small <?php 
                                        echo $payment['status'] == 'paid' ? 'btn-success' : 
                                            ($payment['status'] == 'pending' ? 'btn' : 
                                                ($payment['status'] == 'failed' ? 'btn-danger' : 'btn-secondary')); 
                                    ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($payment['description']); ?></td>
                                <td>
                                    <a href="payments.php?edit=<?php echo $payment['id']; ?>" class="btn btn-small">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add this before the closing </body> tag -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
