<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php'); // Redirect to login page
    exit;
}

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: dashboard.php'); // Redirect to dashboard if not admin
    exit;
}

// Proceed with the existing code to load data and display table
// Load data from database
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    $db->createBranchesTable(); // Ensure table exists
    $branches = $db->getBranches();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if $branches is an array
if (!is_array($branches)) {
    $branches = [];
}
?>

<?php
// Start output buffering to capture content
ob_start();
?>

<div class="card card-flush shadow-sm">
    <div class="card-body py-5">

    <div class="d-flex flex-stack w-100" style="direction: rtl;">
      <input type="text" id="searchBranchInput" placeholder="البحث بالاسم" style="direction: rtl" class="form-control form-control-solid w-100">
    </div>
       
      <!-- begin::Users Table -->
      <div class="table-responsive">
          <table id="branchesTable" class="table table-rounded table-row-dashed table-row-gray-300 gy-4 gs-4" style="direction: rtl;">
          <thead>
            <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
                <th class="text-center fw-bold">اسم الفرع</th>
                <th class="w-50px"></th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <!--end::Users Table-->

    </div>
</div>




<!-- Add Branch modal -->
<div class="modal" id="addBranchModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="addBranchForm" class="form">
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addBranchName" name="addBranchName" required />
                <label for="addBranchName">اسم الفرع</label>
            </div>
          </div>
          <div class="text-center pt-5">
            <button class="btn btn-primary w-100px ms-5" type="submit">حفظ</button>
            <button type="reset" class="btn btn-light w-100px" id="cancelAddBranch">
              إلغاء
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Branch modal -->
<div class="modal" id="editBranchModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="editBranchForm" class="form">
          <input type="hidden" id="editBranchId" />
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="text" class="form-control form-control-solid" placeholder="اسم الفرع" value="" id="editBranchName" name="editBranchName" required />
                <label for="editBranchName">اسم الفرع</label>
            </div>
          </div>
          <div class="text-center pt-5">
            <button type="submit" class="btn btn-primary w-100px ms-5" id="saveEditBranch">حفظ</button>
            <button type="reset" class="btn btn-light w-100px" id="cancelEditBranch">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>

<script>
var initialBranches = <?php echo json_encode($branches); ?>;
</script>
<script src="js/jquery.js"></script>
<script src="js/branchOperations.js"></script>
<script>
  $(document).ready(function() {
    initializeBranches();
  });
</script>

<?php
// Capture the content and include header/footer
$page_content = ob_get_clean();
$page_title = 'إدارة الفروع';
include 'layout/header.php';
echo $page_content;
include 'layout/footer.php';
?>
