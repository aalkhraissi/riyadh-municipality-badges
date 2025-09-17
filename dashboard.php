<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php'); // Redirect to login page
    exit;
}

// Proceed with the existing code to load data and display dashboard
// Load data from database
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    $db->addBranchIdColumnToRecordsTable(); // Ensure branch_id column exists

    // Get user permissions
    $userBranchAccessType = isset($_SESSION['branch_access_type']) ? $_SESSION['branch_access_type'] : 'all_branches';
    $userAssignedBranches = isset($_SESSION['assigned_branches']) ? json_decode($_SESSION['assigned_branches'], true) : null;

    // Load data filtered by user permissions
    $data = $db->getAllFiltered($userBranchAccessType, $userAssignedBranches);
    $branches = $db->getBranches();

    // Filter branches based on user permissions for display
    if ($userBranchAccessType === 'specific_branches' && $userAssignedBranches) {
        $filteredBranches = array_filter($branches, function($branch) use ($userAssignedBranches) {
            return in_array($branch['id'], $userAssignedBranches);
        });
        $branches = array_values($filteredBranches);
    }

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if $data is an array
if (!is_array($data)) {
    $data = [];
}

// Calculate dashboard statistics
$totalRecords = count($data);
$totalBranches = count($branches);
$recordsByBranch = [];
foreach ($data as $record) {
    $branchId = $record['branch_id'] ?? 'unknown';
    if (!isset($recordsByBranch[$branchId])) {
        $recordsByBranch[$branchId] = 0;
    }
    $recordsByBranch[$branchId]++;
}
?>

<?php
// Start output buffering to capture content
ob_start();
?>
<!--begin::Dashboard Content-->
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
  <!--begin::Col-->
  <div class="col-md-4">
    <div class="card card-flush h-md-100">
      <div class="card-header">
        <div class="card-title">
          <h3 class="card-label">إحصائيات عامة</h3>
        </div>
      </div>
      <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center mb-5">
          <div class="symbol symbol-50px me-5">
            <span class="symbol-label bg-light-primary">
              <i class="ki-duotone ki-users fs-2x text-primary">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
              </i>
            </span>
          </div>
          <div class="d-flex flex-column">
            <span class="text-gray-800 fw-bold fs-1"><?php echo $totalRecords; ?></span>
            <span class="text-gray-600 fw-semibold">إجمالي الموظفين</span>
          </div>
        </div>
        <div class="d-flex align-items-center mb-5">
          <div class="symbol symbol-50px me-5">
            <span class="symbol-label bg-light-success">
              <i class="ki-duotone ki-building fs-2x text-success">
                <span class="path1"></span>
                <span class="path2"></span>
              </i>
            </span>
          </div>
          <div class="d-flex flex-column">
            <span class="text-gray-800 fw-bold fs-1"><?php echo $totalBranches; ?></span>
            <span class="text-gray-600 fw-semibold">إجمالي الفروع</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--end::Col-->

  <!--begin::Col-->
  <div class="col-md-8">
    <div class="card card-flush h-md-100">
      <div class="card-header">
        <div class="card-title">
          <h3 class="card-label">توزيع الموظفين حسب الفرع</h3>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
              <tr class="fw-bold text-muted">
                <th class="min-w-150px">اسم الفرع</th>
                <th class="min-w-100px text-center">عدد الموظفين</th>
                <th class="min-w-100px text-center">النسبة المئوية</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($branches as $branch): ?>
                <?php
                  $branchRecordCount = $recordsByBranch[$branch['id']] ?? 0;
                  $percentage = $totalRecords > 0 ? round(($branchRecordCount / $totalRecords) * 100, 1) : 0;
                ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="d-flex justify-content-start flex-column">
                        <a href="#" class="text-gray-900 fw-bold text-hover-primary fs-6"><?php echo htmlspecialchars($branch['name']); ?></a>
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-light-primary fs-7 fw-bold"><?php echo $branchRecordCount; ?></span>
                  </td>
                  <td class="text-center">
                    <div class="d-flex flex-column w-100 me-2">
                      <div class="d-flex flex-stack mb-2">
                        <span class="text-muted me-2 fs-7 fw-semibold"><?php echo $percentage; ?>%</span>
                      </div>
                      <div class="progress h-6px w-100">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!--end::Col-->
</div>
<!--end::Dashboard Content-->

<?php
// Capture the content and include header/footer
$page_content = ob_get_clean();
$page_title = 'لوحة التحكم';
include 'layout/header.php';
echo $page_content;
include 'layout/footer.php';
?>