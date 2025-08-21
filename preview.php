<?php
// Get the record ID from URL
if (!isset($_GET['id'])) {
    die('Record ID not specified.');
}
$recordId = $_GET['id'];

// Load database
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    $record = $db->getById($recordId);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!$record) {
    die('Record not found.');
}

?>


<!DOCTYPE html>
<html lang="en" dir="rtl" style="direction: rtl;">
<!--begin::Head-->

<head>
  <title>Badge</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=0">
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
  <link href="css/plugins.bundle.css" rel="stylesheet" type="text/css" />
  <link href="css/style.bundle.css" rel="stylesheet" type="text/css" />

</head>
<body id="kt_body" class="app-blank bg-white">
  <div class="d-flex flex-column flex-root" id="kt_app_root">
    <div class="d-flex flex-row flex-column-fluid">
      <div class="d-flex flex-column flex-row-fluid w-lg-50 p-10">
        <div class="d-flex flex-center flex-column flex-row-fluid">
          <img class="mx-auto mw-100 w-250px mb-10" src="logo.png" alt="">
          <div class="bg-body d-flex flex-center rounded-4 w-md-600px">
            <div class="d-flex flex-column align-items-center p-0 border-0 mb-10">
				<div class="d-flex flex-column align-items-center w-100">
					<span class="fw-bolder" style="color:#010101; font-size:40px">
                        <?php echo htmlspecialchars(sprintf('%03d', $record['number'])); ?>
                    </span>
					<span class="fw-bolder" style="color:#006a46; font-size:30px">
                        <?php echo htmlspecialchars($record['name']); ?>
                    </span>
					<span class="fw-simibold" style="color:#8ba0d0; font-size:20px">
                        <?php echo htmlspecialchars($record['position']); ?>
                    </span>
					<span class="" style="color:#0d6b30; font-size:15px">
                        <?php echo htmlspecialchars($record['email']); ?>
                    </span>
				</div>
			</div>
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <script src="js/plugins.bundle.js"></script>
  <script src="js/scripts.bundle.js"></script>
</body>
<!--end::Body-->

</html>