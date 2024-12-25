<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "banking_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch account details
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['account_number'])) {
    $account_number = intval($_GET['account_number']);
    $sql = "SELECT * FROM accounts WHERE account_number = $account_number";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "Account not found"]);
    }
}

// Transfer funds
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $from_account = intval($data['fromAccount']);
    $to_account = intval($data['toAccount']);
    $amount = floatval($data['amount']);

    if ($amount <= 0) {
        echo json_encode(["error" => "Invalid transfer amount"]);
        exit;
    }

    // Check sender and receiver accounts
    $sender = $conn->query("SELECT * FROM accounts WHERE account_number = $from_account")->fetch_assoc();
    $receiver = $conn->query("SELECT * FROM accounts WHERE account_number = $to_account")->fetch_assoc();

    if (!$sender || !$receiver) {
        echo json_encode(["error" => "Account not found"]);
        exit;
    }

    if ($sender['balance'] < $amount) {
        echo json_encode(["error" => "Insufficient balance"]);
        exit;
    }

    // Perform transfer
    $conn->query("UPDATE accounts SET balance = balance - $amount WHERE account_number = $from_account");
    $conn->query("UPDATE accounts SET balance = balance + $amount WHERE account_number = $to_account");

    echo json_encode(["message" => "Transfer successful"]);
}

$conn->close();
?>
