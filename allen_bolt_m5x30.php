<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "materials_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $materials_data array
$materials_data = [];

// Fetch data for allen_bolt_m5x30 table
$table = 'allen_bolt_m5x30';
$stmt = $conn->prepare("SELECT id, datetime, qty_in, qty_out, person_in_charge, total_bal_qty FROM allen_bolt_m5x30 ORDER BY datetime ASC");
if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();

$materials_data = [];
while ($row = $result->fetch_assoc()) {
    $materials_data[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $datetime = $_POST['datetime'] ?? null;
    $qty_in = $_POST['qty_in'] ?? 0;
    $qty_out = $_POST['qty_out'] ?? 0;
    $person_in_charge = $_POST['person_in_charge'] ?? '';
    $total_bal_qty = $_POST['total_bal_qty'] ?? 0;

    if ($action === 'update' && $id) {
        $stmt = $conn->prepare("UPDATE allen_bolt_m5x30 SET datetime=?, qty_in=?, qty_out=?, person_in_charge=?, total_bal_qty=? WHERE id=?");
        if ($stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("siissi", $datetime, $qty_in, $qty_out, $person_in_charge, $total_bal_qty, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'insert') {
        $stmt = $conn->prepare("INSERT INTO allen_bolt_m5x30 (datetime, qty_in, qty_out, person_in_charge, total_bal_qty) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("siiss", $datetime, $qty_in, $qty_out, $person_in_charge, $total_bal_qty);
        $stmt->execute();
        $stmt->close();
    }

    echo "Data saved successfully.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>allen_bolt_m5x30</title>
    <link rel="icon" type="image/favicon" href="img/hayakawalogo.png" sizes="any"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(51deg, rgba(2,0,36,1) 0%, rgba(36,52,60,0.2205933988764045) 35%, rgba(0,212,255,1) 100%); /* Light background for contrast */
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 40px;
            right: 20px;
            padding: 10px 20px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .back-button:hover {
            background-color: #333;
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #ddd;
        }

        td {
            border-bottom: 1px solid #ddd;
        }

        input {
            width: 100%;
            box-sizing: border-box;
        }

        .saveBtn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .saveBtn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="container">
    <button class="back-button" onclick="navigateToSearch()">Back to Search</button>

    <form id="allen_bolt_m5x30Form">
        <h2>allen_bolt_m5x30 Table</h2>
        <div class="table-container">
            <table id="allen_bolt_m5x30Table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Quantity In</th>
                        <th>Quantity Out</th>
                        <th>Person In Charge</th>
                        <th>Total Balance Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials_data as $row): ?>
                    <?php
                    $datetime_value = date('Y-m-d\TH:i', strtotime($row['datetime']));
                    ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <td><input type="datetime-local" name="datetime[]" value="<?php echo $datetime_value; ?>" readonly></td>
                        <td><input type="number" name="qty_in[]" value="<?php echo $row['qty_in']; ?>" readonly></td>
                        <td><input type="number" name="qty_out[]" value="<?php echo $row['qty_out']; ?>" readonly></td>
                        <td><input type="text" name="person_in_charge[]" value="<?php echo $row['person_in_charge']; ?>" readonly></td>
                        <td><input type="number" name="total_bal_qty[]" value="<?php echo $row['total_bal_qty']; ?>" readonly></td>
                        <td><button type="button" class="saveBtn" disabled>Saved</button></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr data-id="new">
                        <td><input type="datetime-local" name="datetime[]" id="newDatetime"></td>
                        <td><input type="number" name="qty_in[]" value="0"></td>
                        <td><input type="number" name="qty_out[]" value="0"></td>
                        <td><input type="text" name="person_in_charge[]" placeholder="Name" required></td>
                        <td><input type="number" name="total_bal_qty[]" value="0" readonly></td>
                        <td><button type="button" class="saveBtn" id="newSaveBtn">Save</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateTotalBalQty() {
        const table = document.getElementById('allen_bolt_m5x30Table');
        const rows = table.querySelectorAll('tbody tr');
        let previousTotalBalQty = 0;

        rows.forEach((row, index) => {
            const qtyIn = parseFloat(row.querySelector('input[name="qty_in[]"]').value) || 0;
            const qtyOut = parseFloat(row.querySelector('input[name="qty_out[]"]').value) || 0;
            if (index > 0) {
                previousTotalBalQty = parseFloat(rows[index - 1].querySelector('input[name="total_bal_qty[]"]').value) || 0;
            }
            const totalBalQty = previousTotalBalQty + qtyIn - qtyOut;
            row.querySelector('input[name="total_bal_qty[]"]').value = totalBalQty;
        });
    }

    function setUpEventListeners() {
        document.querySelectorAll('input[name="qty_in[]"], input[name="qty_out[]"]').forEach(input => {
            input.addEventListener('input', updateTotalBalQty);
        });

        document.getElementById('newSaveBtn').addEventListener('click', function() {
            const row = this.closest('tr');
            const inputs = row.querySelectorAll('input');
            const personInCharge = inputs[3].value.trim();

            if (!personInCharge) {
                alert('Person in Charge is required.');
                return; // Prevents saving if field is empty
            }

            const data = {
                action: 'insert',
                id: null,
                datetime: inputs[0].value,
                qty_in: inputs[1].value,
                qty_out: inputs[2].value,
                person_in_charge: personInCharge,
                total_bal_qty: inputs[4].value
            };

            fetch('allen_bolt_m5x30.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            }).then(response => response.text())
              .then(result => {
                  console.log(result);
                  window.location.href = 'SearchBar.php'; // Navigate after saving
              }).catch(error => {
                  console.error('Error:', error);
              });
        });
    }

    function setCurrentDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const datetime = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('newDatetime').value = datetime;
    }

    function scrollToBottom() {
    const tableContainer = document.querySelector('.table-container');
    tableContainer.scrollTop = tableContainer.scrollHeight;
}

    setUpEventListeners();
    updateTotalBalQty();
    setCurrentDateTime();
    scrollToBottom();
});

  
function navigateToSearch() {
    window.location.href = 'SearchBar.php';
}
</script>

</body>
</html>
