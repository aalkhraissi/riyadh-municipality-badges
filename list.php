<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php'); // Redirect to login page
    exit;
}

// Proceed with the existing code to load data and display table
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
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if $data is an array
if (!is_array($data)) {
    $data = [];
}
?>


<?php
// Start output buffering to capture content
ob_start();
?>


<div class="card card-flush shadow-sm">
    <div class="card-body">


       <div class="d-flex flex-stack w-100 mb-5" style="direction: rtl;">
  <div class="fv-row ms-auto w-100">
    <input type="text" id="searchInput" placeholder="البحث بالاسم او بالرقم" style="direction: rtl" class="form-control form-control-solid w-100">
  </div>
  <div class="fv-row ms-5 w-300px">
    <select id="branchFilter" data-control="select2" data-allow-clear="false" data-hide-search="true" class="form-select form-select-solid" style="min-width: 300px;">
      <option value="">جميع الفروع</option>
      <?php foreach ($branches as $branch): ?>
        <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

</div>

<!-- begin::DataTable -->
<div class="table-responsive">
  <table id="dataTable" class="table table-rounded table-row-dashed table-row-gray-300 gy-4 gs-4" style="direction: rtl;">
    <thead>
                  <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">

      <th class="text-center fw-bold w-50px">
        <div class="form-check form-check-sm form-check-custom form-check-solid d-inline-block">
          <input class="form-check-input" type="checkbox" id="selectAll" />
        </div>
      </th>
      <th class="text-center fw-bold w-50px">#</th>
      <th class="text-center fw-bold" style="width: 200px; min-width: 200px;">الاسم</th>
      <th class="text-center fw-bold" style="width: 150px; min-width: 150px;">الإدارة العامة</th>
      <th class="text-center fw-bold" style="width: 150px; min-width: 150px;">الإدارة</th>
      <th class="text-center fw-bold" style="width: 120px; min-width: 120px;">القسم</th>
      <th class="text-center fw-bold" style="width: 200px; min-width: 200px;">البريد الإلكتروني</th>
      <th class="w-50px"></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>
<!--end::DataTable-->

<!-- Pagination controls -->
<div class="d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <select id="rowsPerPage" data-control="select2" data-allow-clear="false" data-hide-search="true" class="form-select form-select-solid form-select-sm w-auto">
      <option value="5">5</option>
      <option value="10" selected>10</option>
      <option value="25">25</option>
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="all">الكل</option>
    </select>
  </div>
  <div class="d-flex align-items-center">
    <div class="me-5" id="recordsInfo" style="direction: rtl;"></div>
    <ul class="pagination align-items-center mb-0" id="paginationContainer">
      <li class="page-item first disabled mx-0" id="firstPage">
        <a href="#" class="page-link">
          <i class="ki-duotone ki-double-right fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          </i>
        </a>
      </li>
      <li class="page-item previous disabled mx-0" id="prevPage">
        <a href="#" class="page-link">
           <i class="ki-duotone ki-right fs-1"></i>
        </a>
      </li>
      <!-- Page numbers will be inserted here by JavaScript -->
      <li class="page-item next disabled mx-0" id="nextPage">
        <a href="#" class="page-link">
          <i class="ki-duotone ki-left fs-1"></i>
        </a>
      </li>
      <li class="page-item last disabled mx-0" id="lastPage">
        <a href="#" class="page-link ">
           <i class="ki-duotone ki-double-left fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          </i>
        </a>
      </li>
    </ul>
  </div>
</div>


    </div>
</div>



<!-- Add modal -->
<div class="modal" id="addModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="addForm" class="form">
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="text" class="form-control form-control-solid" placeholder="الرقم" value="" id="addNumber" name="addNumber" readonly />
                <label for="addNumber">&nbsp;&nbsp;&nbsp;الرقم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addName" name="addName" required />
                <label for="addName">&nbsp;&nbsp;&nbsp;الاسم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
                <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addGeneralAdministration" name="addGeneralAdministration" />
                <label for="addGeneralAdministration">الإدارة العامة</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
                <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addAdministration" name="addAdministration" required />
                <label for="addAdministration">الإدارة</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
                <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addDepartment" name="addDepartment" />
                <label for="addDepartment">&nbsp;&nbsp;&nbsp;القسم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
                <input type="email" class="form-control form-control-solid" placeholder=" " value="" id="addEmail" name="addEmail" required />
                <label for="addEmail">البريد الإلكتروني</label>
            </div>
          </div>
          <div class="text-center pt-5">
            <button class="btn btn-primary w-100px ms-5" type="submit">حفظ</button>
            <button type="reset" class="btn btn-light w-100px" id="cancelAdd">
              إلغاء
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit modal -->
<div class="modal" id="editModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="editForm" class="form">
          <input type="hidden" id="editId" />
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="text" class="form-control form-control-solid" placeholder="الاسم" value="" id="editName" name="editName" required />
                <label for="editName">&nbsp;&nbsp;&nbsp;الاسم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="text" class="form-control form-control-solid" placeholder="الإدارة العامة" value="" id="editGeneralAdministration" name="editGeneralAdministration" />
                <label for="editGeneralAdministration">الإدارة العامة</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="text" class="form-control form-control-solid" placeholder="الإدارة" value="" id="editAdministration" name="editAdministration" required />
                <label for="editAdministration">الإدارة</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="text" class="form-control form-control-solid" placeholder="القسم" value="" id="editDepartment" name="editDepartment" />
                <label for="editDepartment">&nbsp;&nbsp;&nbsp;القسم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
            <input type="email" class="form-control form-control-solid" placeholder="البريد الإلكتروني" value="" id="editEmail" name="editEmail" required />
                <label for="editEmail">البريد الإلكتروني</label>
            </div>
          </div>
          <div class="text-center pt-5">
            <button type="submit" class="btn btn-primary w-100px ms-5" id="saveEdit">حفظ</button>
            <button type="reset" class="btn btn-light w-100px" id="cancelEdit">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Import Progress Modal -->
<div class="modal fade" id="importProgressModal" style="z-index: 1055;" tabindex="-1" aria-labelledby="importProgressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="modal-title mb-2" id="importProgressModalLabel">Importing CSV</h5>
        <p id="importProgressText" class="mb-3">Preparing to import...</p>
        <div class="progress" style="height: 20px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="importProgressBar" style="width: 0%"></div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>

</div>

<script>
var initialData = <?php echo json_encode($data); ?>;
var initialBranches = <?php echo json_encode($branches); ?>;
</script>
<script src="js/jquery.js"></script>

<!-- Initialize the app -->
<script src="js/readOperations.js"></script>
<script src="js/operationFunctions.js"></script>
<script>
  $(document).ready(function() {
    initialize();

    // Initialize branch filter change handler
    $('#branchFilter').on('change', function() {
      var selectedBranch = $(this).val();
      // Save selected branch to localStorage
      localStorage.setItem('selectedBranch', selectedBranch);
      // Handle branch filter change
      filterData();
      renderTable();
      // Update the next number for the selected branch
      setNextNumber();
    });

    // Restore selected branch from localStorage on page load
    setTimeout(function() {
      var savedBranch = localStorage.getItem('selectedBranch');
      console.log('Checking for saved branch selection:', savedBranch);
      if (savedBranch) {
        console.log('Restoring branch selection:', savedBranch);
        $('#branchFilter').val(savedBranch).trigger('change.select2');
        console.log('Branch filter value after restoration:', $('#branchFilter').val());
        // Re-apply filtering after restoration
        filterData();
        renderTable();
      } else {
        console.log('No saved branch selection found');
      }

      // Ensure rows per page event handler is attached
      console.log('Ensuring rows per page event handler is attached');
      $('#rowsPerPage').off('change').on('change', function() {
        console.log('Rows per page changed (fallback handler):', $(this).val());
        var newRowsPerPage = $(this).val();

        // Update select2 display
        $('#rowsPerPage').trigger('change.select2');

        if (newRowsPerPage === "all") {
          rowsPerPage = filteredData.length;
          currentPage = 1;
          renderTable();
        } else {
          var rows = parseInt(newRowsPerPage);
          if (!isNaN(rows) && rows > 0) {
            rowsPerPage = rows;
            currentPage = 1;
            renderTable();
          }
        }
      });
    }, 200);
  });
</script>


<?php
// Capture the content and include header/footer
$page_content = ob_get_clean();
$page_title = 'قائمة الموظفين';
include 'layout/header.php';
echo $page_content;
include 'layout/footer.php';
?>