<?php 
include 'rds.php';

// Database connection management
function connectToDatabase($isReadOnly = false) {
    // Use read-only server for read operations
    $server = $isReadOnly ? DB_SERVER_RO : DB_SERVER;
    
    $connection = mysqli_connect($server, DB_USERNAME, DB_PASSWORD);
    
    if (mysqli_connect_errno()) {
        throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
    }
    
    mysqli_select_db($connection, DB_DATABASE);
    return $connection;
}

// Check whether the table exists and, if not, create it
function verifyOrdersTable($connection) {
    if(!tableExists("ORDERS", $connection, DB_DATABASE)) {
        $query = "CREATE TABLE ORDERS (
            ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            NAME VARCHAR(45),
            COFFEE VARCHAR(20),
            MILK VARCHAR(20),
            SIZE VARCHAR(20),
            QTY VARCHAR(20),
            ORDER_TIME TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if(!mysqli_query($connection, $query)) {
            throw new Exception("Error creating table.");
        }
    }
}

// Check for the existence of a table
function tableExists($tableName, $connection, $dbName) {
    $t = mysqli_real_escape_string($connection, $tableName);
    $d = mysqli_real_escape_string($connection, $dbName);

    $checktable = mysqli_query($connection,
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");

    return (mysqli_num_rows($checktable) > 0);
}

// Add an order to the table - WRITE OPERATION
function addOrder($connection, $name, $coffee, $milk, $size, $qty) {
    $n = mysqli_real_escape_string($connection, $name);
    $c = mysqli_real_escape_string($connection, $coffee);
    $m = mysqli_real_escape_string($connection, $milk);
    $s = mysqli_real_escape_string($connection, $size);
    $q = mysqli_real_escape_string($connection, $qty);

    $query = "INSERT INTO ORDERS (NAME, COFFEE, MILK, SIZE, QTY) VALUES ('$n', '$c', '$m', '$s', '$q');";

    if(!mysqli_query($connection, $query)) {
        throw new Exception("Error adding order data.");
    }
    
    // Return true if successful
    return true;
}

// Get orders with pagination - READ OPERATION
function getOrders($connection, $page = 1, $ordersPerPage = 15) {
    $offset = ($page - 1) * $ordersPerPage;
    
    $query = "SELECT * FROM ORDERS ORDER BY ID DESC LIMIT $ordersPerPage OFFSET $offset";
    $result = mysqli_query($connection, $query);
    
    $orders = [];
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    mysqli_free_result($result);
    return $orders;
}

// Count total orders for pagination - READ OPERATION
function countTotalOrders($connection) {
    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM ORDERS");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Process form submission
$orderSubmitted = false;
$writeConnection = null;
$readConnection = null;
$orders = [];
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$ordersPerPage = 15;
$totalOrders = 0;
$totalPages = 0;

try {
    // Use write connection for table verification (potential table creation)
    $writeConnection = connectToDatabase(false);
    verifyOrdersTable($writeConnection);
    
    // Use read connection for fetching data
    $readConnection = connectToDatabase(true);
    
	// Process form submission
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		// Check if it's an AJAX request for order submission
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
			header('Content-Type: application/json');
			
			$name = isset($_POST['NAME']) ? $_POST['NAME'] : '';
			$coffee = isset($_POST['COFFEE']) ? $_POST['COFFEE'] : '';
			$milk = isset($_POST['MILK']) ? $_POST['MILK'] : '';
			$size = isset($_POST['SIZE']) ? $_POST['SIZE'] : '';
			$qty = isset($_POST['QTY']) ? $_POST['QTY'] : '';
			
			if (!empty($name)) {
				try {
                    // Use write connection for inserting data
					$orderSubmitted = addOrder($writeConnection, $name, $coffee, $milk, $size, $qty);
					// Don't return all orders, just confirm success
					echo json_encode(['success' => true]);
				} catch (Exception $e) {
					echo json_encode(['success' => false, 'message' => $e->getMessage()]);
				}
			} else {
				echo json_encode(['success' => false, 'message' => 'Name is required']);
			}
			
			exit; // Stop execution after handling AJAX request
		}
        
        // Regular form submission
        $name = isset($_POST['NAME']) ? htmlentities($_POST['NAME']) : '';
        $coffee = isset($_POST['COFFEE']) ? htmlentities($_POST['COFFEE']) : '';
        $milk = isset($_POST['MILK']) ? htmlentities($_POST['MILK']) : '';
        $size = isset($_POST['SIZE']) ? htmlentities($_POST['SIZE']) : '';
        $qty = isset($_POST['QTY']) ? htmlentities($_POST['QTY']) : '';
        
        if (!empty($name)) {
            // Use write connection for inserting data
            $orderSubmitted = addOrder($writeConnection, $name, $coffee, $milk, $size, $qty);
        }
    }
    
    // Get orders with pagination - Use read connection
    $totalOrders = countTotalOrders($readConnection);
    $totalPages = ceil($totalOrders / $ordersPerPage);
    
    // Ensure current page is valid
    if ($currentPage < 1) $currentPage = 1;
    if ($totalPages > 0 && $currentPage > $totalPages) $currentPage = $totalPages;
    
    // Get all orders for the current page - Use read connection
    $orders = getOrders($readConnection, $currentPage, $ordersPerPage);
    
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
} finally {
    // Clean up database connections
    if ($writeConnection) {
        mysqli_close($writeConnection);
    }
    if ($readConnection) {
        mysqli_close($readConnection);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AWS Coffee Order System</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            /* AWS Color Palette */
            --aws-squid-ink: #232f3e;
            --aws-anchor: #0073bb;
            --aws-smile-orange: #ff9900;
            --aws-ripe-lemon: #ff9900;
            --aws-observatory: #545b64;
            --aws-squid-ink-light: #31465f;
            --aws-background: #f2f3f3;
            --aws-success: #1d8102;
            --aws-border: #d5dbdb;
            --aws-error: #d13212;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Amazon Ember', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        body {
            background-color: var(--aws-background);
            color: var(--aws-squid-ink);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .app-header {
            background-color: var(--aws-squid-ink);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        
        .app-header h1 {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .app-header h1 i {
            color: var(--aws-smile-orange);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: var(--aws-squid-ink-light);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 500;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header i {
            color: var(--aws-smile-orange);
            margin-right: 0.75rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--aws-squid-ink);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid var(--aws-border);
            border-radius: 4px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--aws-anchor);
            box-shadow: 0 0 0 3px rgba(0, 115, 187, 0.15);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out, transform 0.1s ease;
            text-decoration: none;
            gap: 0.5rem;
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .btn-primary {
            background-color: var(--aws-anchor);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #005d95;
        }
        
        .btn-submit {
            background-color: var(--aws-smile-orange);
            color: white;
            width: 100%;
            margin-top: 0.75rem;
        }
        
        .btn-submit:hover {
            background-color: #e68a00;
        }
        
        .btn-secondary {
            background-color: var(--aws-observatory);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #444a52;
        }
        
        .orders-section {
            margin-top: 1.5rem;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
        }
        
        .table th {
            background-color: var(--aws-squid-ink);
            color: white;
            font-weight: 500;
        }
        
        .table tbody tr {
            border-bottom: 1px solid var(--aws-border);
        }
        
        .table tbody tr:last-child {
            border-bottom: none;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 115, 187, 0.05);
        }
        
        .alert {
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            position: relative;
            animation: slideIn 0.3s ease-out forwards;
        }
        
        .alert-success {
            background-color: rgba(29, 129, 2, 0.1);
            border-left: 4px solid var(--aws-success);
            color: var(--aws-success);
        }
        
        .alert-error {
            background-color: rgba(209, 50, 18, 0.1);
            border-left: 4px solid var(--aws-error);
            color: var(--aws-error);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 0.5rem;
        }
        
        .page-item {
            list-style: none;
        }
        
        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 0.75rem;
            color: var(--aws-anchor);
            background-color: white;
            border: 1px solid var(--aws-border);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .page-link:hover {
            background-color: #f5f5f5;
            border-color: var(--aws-anchor);
        }
        
        .page-item.active .page-link {
            background-color: var(--aws-anchor);
            border-color: var(--aws-anchor);
            color: white;
            cursor: default;
        }
        
        .page-item.disabled .page-link {
            color: var(--aws-observatory);
            pointer-events: none;
            cursor: not-allowed;
            background-color: white;
            border-color: var(--aws-border);
            opacity: 0.6;
        }
        
        .form-row {
            margin-bottom: 1.5rem;
        }
        
        .loading-spinner {
            display: none;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; visibility: hidden; }
        }
        
        .fade-out {
            animation: fadeOut 5s forwards;
        }
        
        /* Responsive layout */
        @media (min-width: 992px) {
            .grid {
                display: grid;
                grid-template-columns: 1fr 1.5fr;
                gap: 2rem;
            }
        }
        
        @media (max-width: 991px) {
            .container {
                padding: 1.5rem;
            }
            
            .grid {
                display: flex;
                flex-direction: column;
            }
            
            .card-header {
                padding: 0.85rem 1.25rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .table th, .table td {
                padding: 0.85rem;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }
            
            .app-header {
                padding: 1rem;
            }
            
            .card-header {
                padding: 0.75rem 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table th, .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 0.65rem 1.25rem;
            }
        }
        
        /* Status Indicator */
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .status-indicator.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .status-success {
            background-color: var(--aws-success);
            color: white;
        }
        
        .status-error {
            background-color: var(--aws-error);
            color: white;
        }
    </style>
</head>

<body>
    <header class="app-header">
        <h1><i class="fas fa-coffee"></i> AWS Coffee Order System</h1>
    </header>
    
    <div class="container">
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($orderSubmitted): ?>
            <div class="alert alert-success fade-out">
                <strong>Success!</strong> Your order has been placed successfully.
            </div>
        <?php endif; ?>
        
        <div class="grid">
            <!-- Order Form -->
            <div class="card">
                <div class="card-header">
                    <span><i class="fas fa-shopping-cart"></i> Place Your Order</span>
                </div>
                <div class="card-body">
                    <form id="orderForm">
                        <div class="form-group">
                            <label class="form-label" for="name">Your Name</label>
                            <input type="text" id="name" name="NAME" class="form-control" placeholder="Enter your name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="coffee">Coffee Type</label>
                            <select class="form-control" id="coffee" name="COFFEE" required>
                                <option value="">Select Coffee</option>
                                <option value="Flat White">Flat White</option>
                                <option value="Americano">Americano</option>
                                <option value="Macchiato">Macchiato</option>
                                <option value="Cappuccino">Cappuccino</option>
                                <option value="Latte">Latte</option>
                                <option value="Mocha">Mocha</option>
                                <option value="Espresso">Espresso</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="milk">Milk Type</label>
                                <select class="form-control" id="milk" name="MILK" required>
                                    <option value="">Select Milk</option>
                                    <option value="Full Cream">Full Cream</option>
                                    <option value="Skinny">Skinny</option>
                                    <option value="Soy">Soy</option>
                                    <option value="Almond">Almond</option>
                                    <option value="Oat">Oat</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row" style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label class="form-label" for="size">Size</label>
                                <select class="form-control" id="size" name="SIZE" required>
                                    <option value="">Select Size</option>
                                    <option value="Small">Small</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Large">Large</option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label class="form-label" for="qty">Quantity</label>
                                <select class="form-control" id="qty" name="QTY" required>
                                    <option value="">Select Quantity</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            <span class="loading-spinner"><i class="fas fa-spinner"></i></span>
                            Place Order
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Orders List -->
            <div class="card orders-section">
                <div class="card-header">
                    <span><i class="fas fa-clipboard-list"></i> Recent Orders</span>
                    <button class="btn btn-secondary btn-sm" id="refreshOrders">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Coffee</th>
                                    <th>Milk</th>
                                    <th>Size</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['ID']); ?></td>
                                    <td><?php echo htmlspecialchars($order['NAME']); ?></td>
                                    <td><?php echo htmlspecialchars($order['COFFEE']); ?></td>
                                    <td><?php echo htmlspecialchars($order['MILK']); ?></td>
                                    <td><?php echo htmlspecialchars($order['SIZE']); ?></td>
                                    <td><?php echo htmlspecialchars($order['QTY']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($orders) === 0): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No orders found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <ul class="pagination">
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" <?php echo ($currentPage <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" <?php echo ($currentPage >= $totalPages) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status indicator for ajax operations -->
    <div id="statusIndicator" class="status-indicator"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderForm = document.getElementById('orderForm');
            const refreshBtn = document.getElementById('refreshOrders');
            const ordersTableBody = document.getElementById('ordersTableBody');
            const loadingSpinner = document.querySelector('.loading-spinner');
            const statusIndicator = document.getElementById('statusIndicator');
            
            // Function to show status message
            function showStatus(message, type) {
                statusIndicator.textContent = message;
                statusIndicator.className = 'status-indicator show status-' + type;
                
                setTimeout(() => {
                    statusIndicator.classList.remove('show');
                }, 3000);
            }
            
            // Function to refresh orders table
            function refreshOrders() {
                fetch('?refresh=true')
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newOrdersTable = doc.getElementById('ordersTableBody');
                        
                        if (newOrdersTable) {
                            ordersTableBody.innerHTML = newOrdersTable.innerHTML;
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing orders:', error);
                        showStatus('Failed to refresh orders', 'error');
                    });
            }
            
            // Order form submission
            orderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading spinner
                loadingSpinner.style.display = 'inline-block';
                
                // Get form data
                const formData = new FormData(orderForm);
                formData.append('ajax', 'true');
                
                // Send AJAX request
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loadingSpinner.style.display = 'none';
                    
                    if (data.success) {
                        // Show success message
                        showStatus('Order placed successfully!', 'success');
                        
                        // Reset form
                        orderForm.reset();
                        
                        // Refresh orders table
                        refreshOrders();
                    } else {
                        // Show error message
                        showStatus(data.message || 'Failed to place order', 'error');
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    showStatus('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
            });
            
            // Refresh button click
            refreshBtn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                icon.classList.add('fa-spin');
                
                refreshOrders();
                
                setTimeout(() => {
                    icon.classList.remove('fa-spin');
                }, 1000);
            });
            
            // Auto-refresh orders every 30 seconds
            setInterval(refreshOrders, 30000);
        });
    </script>
</body>
</html>