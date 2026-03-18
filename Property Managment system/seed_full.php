<?php
require_once __DIR__ . "/db.php";

$pdo = get_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function rand_phone(): string
{
    $a = rand(200, 999);
    $b = rand(200, 999);
    $c = rand(1000, 9999);
    return "($a) $b-$c";
}

function rand_email(string $first, string $last): string
{
    $domains = ["email.com", "cowboyprops.com", "mail.com", "example.com"];
    $d = $domains[array_rand($domains)];
    return strtolower($first . "." . $last . rand(1, 99) . "@" . $d);
}

function pick(array $arr)
{
    return $arr[array_rand($arr)];
}

try {
    $pdo->beginTransaction();

    // Clear child tables first to satisfy FK constraints
    $pdo->exec("DELETE FROM task");
    $pdo->exec("DELETE FROM assignment");
    $pdo->exec("DELETE FROM payment");
    $pdo->exec("DELETE FROM maintenance");
    $pdo->exec("DELETE FROM lease");
    $pdo->exec("DELETE FROM unit");
    $pdo->exec("DELETE FROM property");
    $pdo->exec("DELETE FROM renter");
    $pdo->exec("DELETE FROM employee");

    $firstNames = ["Addison", "Jordan", "Taylor", "Morgan", "Casey", "Riley", "Avery", "Cameron", "Drew", "Sam", "Alex", "Jamie"];
    $lastNames  = ["Smith", "Johnson", "Brown", "Davis", "Miller", "Wilson", "Moore", "Taylor", "Anderson", "Thomas", "Martin"];
    $roles = ["Manager", "Leasing Agent", "Maintenance Lead", "Accountant", "Assistant Manager"];

    $employeeIds = [];
    for ($i = 1; $i <= 20; $i++) {
        $first = pick($firstNames);
        $last  = pick($lastNames);

        $insertEmp = $pdo->prepare(" 
            INSERT INTO employee (Lastname, Firstname, Position, Password, Email, phone)
            VALUES (:lastname, :firstname, :position, :password, :email, :phone)
        ");
        $insertEmp->execute([
            ':lastname' => $last,
            ':firstname' => $first,
            ':position' => pick($roles),
            ':password' => password_hash("TempPass123!", PASSWORD_DEFAULT),
            ':email' => rand_email($first, $last),
            ':phone' => rand_phone(),
        ]);

        $employeeIds[] = (int)$pdo->lastInsertId();
    }

    $propertyIds = [];
    for ($i = 1; $i <= 10; $i++) {
        $address = rand(100, 9999) . " " . pick(["Cedar St", "Oak Ave", "Pine Rd", "Maple Dr", "Birch Ln"]) . ", Stillwater, OK";
        $managerEmpId = $employeeIds[($i - 1) % count($employeeIds)];

        $insertProperty = $pdo->prepare(" 
            INSERT INTO property (Address, ManagerEmpID, Unit_Count)
            VALUES (:address, :manager_emp_id, :unit_count)
        ");
        $insertProperty->execute([
            ':address' => $address,
            ':manager_emp_id' => $managerEmpId,
            ':unit_count' => 2,
        ]);

        $propertyIds[] = (int)$pdo->lastInsertId();
    }

    $unitIds = [];
    $unitRentById = [];
    foreach ($propertyIds as $pid) {
        for ($u = 1; $u <= 2; $u++) {
            $beds = pick([1, 2, 3]);
            $baths = $beds === 1 ? 1 : pick([1, 2]);
            $rent = 1100 + ($beds * 250) + ($baths * 75) + rand(0, 150);

            $insertUnit = $pdo->prepare(" 
                INSERT INTO unit (PropertyID, Unit_number, Bed, bath, price)
                VALUES (:property_id, :unit_number, :bed, :bath, :price)
            ");
            $insertUnit->execute([
                ':property_id' => $pid,
                ':unit_number' => chr(64 + (($pid % 26) ?: 1)) . rand(100, 599),
                ':bed' => $beds,
                ':bath' => $baths,
                ':price' => $rent,
            ]);

            $unitId = (int)$pdo->lastInsertId();
            $unitIds[] = $unitId;
            $unitRentById[$unitId] = $rent;
        }
    }

    $renterIds = [];

    $requiredRenters = [
        [
            'Firstname' => 'Brandon',
            'Lastname' => 'Smith',
            'email' => 'bsmith@cowboyproperties.com',
            'phone' => '555-210-1001',
            'password' => 'mysecret',
        ],
        [
            'Firstname' => 'Paige',
            'Lastname' => 'Jones',
            'email' => 'pjones@cowboyproperties.com',
            'phone' => '555-210-1002',
            'password' => 'acrobat',
        ],
    ];

    $insertRenter = $pdo->prepare(" 
        INSERT INTO renter (Firstname, Lastname, email, phone, password)
        VALUES (:firstname, :lastname, :email, :phone, :password)
    ");

    foreach ($requiredRenters as $requiredRenter) {
        $insertRenter->execute([
            ':firstname' => $requiredRenter['Firstname'],
            ':lastname' => $requiredRenter['Lastname'],
            ':email' => $requiredRenter['email'],
            ':phone' => $requiredRenter['phone'],
            ':password' => password_hash($requiredRenter['password'], PASSWORD_DEFAULT),
        ]);
        $renterIds[] = (int)$pdo->lastInsertId();
    }

    while (count($renterIds) < 20) {
        $first = pick($firstNames);
        $last  = pick($lastNames);

        $insertRenter->execute([
            ':firstname' => $first,
            ':lastname' => $last,
            ':email' => rand_email($first, $last),
            ':phone' => rand_phone(),
            ':password' => password_hash("TempPass123!", PASSWORD_DEFAULT),
        ]);

        $renterIds[] = (int)$pdo->lastInsertId();
    }

    $leaseIds = [];
    $leaseRentById = [];
    for ($i = 0; $i < 20; $i++) {
        $renterId = $renterIds[$i];
        $unitId = $unitIds[$i % count($unitIds)];
        $empId = $employeeIds[$i % count($employeeIds)];
        $rent = (float)($unitRentById[$unitId] ?? 1450.00);
        $periodMonths = 12;

        $insertLease = $pdo->prepare(" 
            INSERT INTO lease (RenterID, EmpID, UnitID, Price, period)
            VALUES (:renter_id, :emp_id, :unit_id, :price, :period)
        ");
        $insertLease->execute([
            ':renter_id' => $renterId,
            ':emp_id' => $empId,
            ':unit_id' => $unitId,
            ':price' => $rent,
            ':period' => $periodMonths,
        ]);

        $leaseId = (int)$pdo->lastInsertId();
        $leaseIds[] = $leaseId;
        $leaseRentById[$leaseId] = $rent;
    }

    // Use first accountant if available for payment records; fallback to first employee
    $paymentEmpId = $employeeIds[0] ?? 1;
    $acctStmt = $pdo->query("SELECT EmpID FROM employee WHERE Position = 'Accountant' ORDER BY EmpID ASC LIMIT 1");
    $acctId = $acctStmt ? (int)$acctStmt->fetchColumn() : 0;
    if ($acctId > 0) {
        $paymentEmpId = $acctId;
    }

    for ($i = 0; $i < min(20, count($leaseIds)); $i++) {
        $leaseId = $leaseIds[$i];
        $renterId = $renterIds[$i];
        $rent = (float)($leaseRentById[$leaseId] ?? 1450.00);

        for ($p = 1; $p <= 2; $p++) {
            $dt = (new DateTime())->modify("-" . (30 * $p) . " days");
            $period = (int)$dt->format('Ym');

            $insertPayment = $pdo->prepare(" 
                INSERT INTO payment (RenterID, LeaseID, EmpID, period, date, amount)
                VALUES (:renter_id, :lease_id, :emp_id, :period, :date, :amount)
            ");
            $insertPayment->execute([
                ':renter_id' => $renterId,
                ':lease_id' => $leaseId,
                ':emp_id' => $paymentEmpId,
                ':period' => $period,
                ':date' => $dt->format('Y-m-d'),
                ':amount' => $rent,
            ]);
        }
    }

    $ticketStatuses = ["Open", "In Progress", "Closed"];
    $descs = [
        "Leaky faucet in kitchen",
        "AC not cooling",
        "Heater making noise",
        "Garbage disposal jammed",
        "Light fixture flickering",
        "Window won’t close fully",
        "Dishwasher leaking",
        "Door lock stuck",
        "Ceiling stain appearing",
        "Smoke detector beeping"
    ];

    $maintenanceEmpId = $employeeIds[0] ?? 1;
    $maintLeadStmt = $pdo->query("SELECT EmpID FROM employee WHERE Position LIKE 'Maintenance%' ORDER BY EmpID ASC LIMIT 1");
    $maintLeadId = $maintLeadStmt ? (int)$maintLeadStmt->fetchColumn() : 0;
    if ($maintLeadId > 0) {
        $maintenanceEmpId = $maintLeadId;
    }

    $ticketIds = [];
    for ($i = 0; $i < 20; $i++) {
        $insertMaint = $pdo->prepare(" 
            INSERT INTO maintenance (UnitID, RenterID, EmpID, Date, Issue, Status)
            VALUES (:unit_id, :renter_id, :emp_id, :date, :issue, :status)
        ");
        $insertMaint->execute([
            ':unit_id' => pick($unitIds),
            ':renter_id' => pick($renterIds),
            ':emp_id' => $maintenanceEmpId,
            ':date' => (new DateTime())->modify("-" . rand(0, 90) . " days")->format('Y-m-d'),
            ':issue' => pick($descs),
            ':status' => pick($ticketStatuses),
        ]);

        $ticketIds[] = (int)$pdo->lastInsertId();
    }

    // Optional tables from your schema
    $insertAssign = $pdo->prepare(" 
        INSERT INTO assignment (EmpID, PropertyID)
        VALUES (:emp_id, :property_id)
    ");
    for ($i = 0; $i < min(count($employeeIds), count($propertyIds)); $i++) {
        $insertAssign->execute([
            ':emp_id' => $employeeIds[$i],
            ':property_id' => $propertyIds[$i],
        ]);
    }

    $insertTask = $pdo->prepare(" 
        INSERT INTO task (TicketID, EmployeeID)
        VALUES (:ticket_id, :employee_id)
    ");
    for ($i = 0; $i < min(10, count($ticketIds)); $i++) {
        $insertTask->execute([
            ':ticket_id' => $ticketIds[$i],
            ':employee_id' => $maintenanceEmpId,
        ]);
    }

    $pdo->commit();
    echo "<h2>Seed complete ✅</h2>";
    echo "<p>Inserted sample rows into employee, property, unit, renter, lease, payment, maintenance, assignment, and task.</p>";
    echo "<p>Required test renter users created: <strong>bsmith</strong> / <strong>mysecret</strong> and <strong>pjones</strong> / <strong>acrobat</strong>.</p>";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h2>Seed failed ❌</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
