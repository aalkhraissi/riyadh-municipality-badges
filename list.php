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
    $data = $db->getAll();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if $data is an array
if (!is_array($data)) {
    $data = [];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title>Badge control</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=0">
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
  <link href="css/plugins.bundle.css" rel="stylesheet" type="text/css" />
  <link href="css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank bg-white">
<div class="d-flex flex-column flex-root" id="kt_app_root">
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
<div class="d-flex flex-row-fluid">
<div class="d-flex flex-column flex-center p-10 w-100 h-100">
<div class="d-flex flex-column flex-column-fluid flex-center w-100 p-0 mx-auto h-100">
<div class="d-flex justify-content-between flex-column-fluid flex-column flex-center w-100">




<div class="w-900px">

<!--begin::Header-->
<div class="d-flex flex-stack align-items-center mb-5" id="header_dev">

  <div class="">
    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
    <a href="logout.php" class="btn btn-danger">تسجيل الخروج</a>
    <?php endif; ?>
  </div>
  <div class=""></div>
  <div class="fs-1" style="direction: rtl;">
    أهلاً بك، <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?>
  </div>
</div>
<div class="separator separator-dashed my-10"></div>
<!--end::Header-->



<div class="d-flex flex-stack w-100 mb-5" style="direction: rtl;">
  <div class="fv-row ms-auto w-100">
    <input type="text" id="searchInput" placeholder="البحث بالاسم او بالرقم" style="direction: rtl" class="form-control form-control-solid w-100">
  </div>
</div>

<!-- begin::DataTable -->
<div class="table-responsive">
  <table id="dataTable" class="table table-bordered w-100" style="direction: rtl;">
    <thead>
      <th class="text-center fw-bold w-50px">
        <div class="form-check form-check-sm form-check-custom form-check-solid d-inline-block">
          <input class="form-check-input" type="checkbox" id="selectAll" />
        </div>
      </th>
      <th class="text-center fw-bold w-50px">#</th>
      <th class="text-center fw-bold">الاسم</th>
      <th class="text-center fw-bold">المسمى الوظيفي</th>
      <th class="text-center fw-bold">البريد الإلكتروني</th>
      <th class="w-200px">
        <div class="d-flex flex-center">
         <form id="importForm" enctype="multipart/form-data">
          <label class="btn btn-icon btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="bottom" title="رفع الملف">
            <i class="ki-duotone ki-cloud-add fs-1">
            <span class="path1"></span>
            <span class="path2"></span>
            </i>
            <input type="file" id="csvFileInput" name="csvfile" accept=".csv" style="display:none;" />
          </label>
        </form>

        <button type="button" class="btn btn-success btn-sm btn-icon mx-5" id="exportBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="تحميل الملف">
          <i class="ki-duotone ki-cloud-download fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          </i>
        </button>

        <button type="button" class="btn btn-secondary btn-sm btn-icon ms-5" id="downloadSelectedBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="تحميل رموز QR المحددة" disabled>
          <i class="ki-duotone ki-scan-barcode fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          <span class="path3"></span>
          <span class="path4"></span>
          <span class="path5"></span>
          <span class="path6"></span>
          <span class="path7"></span>
          <span class="path8"></span>
          </i>
        </button>

        <button type="button" class="btn btn-primary btn-sm btn-icon addBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="إضافة موظف جديد">
          <i class="ki-duotone ki-abstract-10 fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          </i>
        </button>
    </div>
      </th>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>
<!--end::DataTable-->

<!-- Pagination controls -->
<div class="d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <select id="rowsPerPage" class="form-select form-select-solid form-select-sm w-auto">
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
          <i class="ki-duotone ki-double-left fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          </i>
        </a>
      </li>
      <li class="page-item previous disabled mx-0" id="prevPage">
        <a href="#" class="page-link">
           <i class="ki-duotone ki-left fs-1"></i>
        </a>
      </li>
      <!-- Page numbers will be inserted here by JavaScript -->
      <li class="page-item next disabled mx-0" id="nextPage">
        <a href="#" class="page-link">
          <i class="ki-duotone ki-right fs-1"></i>
        </a>
      </li>
      <li class="page-item last disabled mx-0" id="lastPage">
        <a href="#" class="page-link ">
           <i class="ki-duotone ki-double-right fs-1">
          <span class="path1"></span>
          <span class="path2"></span>
          </i>
        </a>
      </li>
    </ul>
  </div>
</div>

<!-- Add modal -->
<div class="modal" id="addModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="addForm" class="form">
          <div class="fv-row mb-5">
            <input type="text" class="form-control form-control-solid" placeholder="الرقم" value="" id="addNumber" name="addNumber" readonly />
          </div>
          <div class="fv-row mb-5">
            <input type="text" class="form-control form-control-solid" placeholder="الاسم" value="" id="addName" name="addName" required />
          </div>
          <div class="fv-row mb-5">
            <input type="text" class="form-control form-control-solid" placeholder="المسمى الوظيفي" value="" id="addPosition" name="addPosition" required />
          </div>
          <div class="fv-row mb-5">
            <input type="email" class="form-control form-control-solid" placeholder="البريد الإلكتروني" value="" id="addEmail" name="addEmail" required />
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
            <input type="text" class="form-control form-control-solid" placeholder="الاسم" value="" id="editName" name="editName" required />
          </div>
          <div class="fv-row mb-5">
            <input type="text" class="form-control form-control-solid" placeholder="المسمى الوظيفي" value="" id="editPosition" name="editPosition" required />
          </div>
          <div class="fv-row mb-5">
            <input type="email" class="form-control form-control-solid" placeholder="البريد الإلكتروني" value="" id="editEmail" name="editEmail" required />
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

</div>



<script>
var initialData = <?php echo json_encode($data); ?>;
</script>
<script src="js/jquery.js"></script>

<!-- Initialize the app -->
<script src="js/readOperations.js"></script>
<script src="js/operationFunctions.js"></script>
<script>
  $(document).ready(function() {
    initialize();
  });
</script>

<div class="d-flex flex-column w-900px">
    <div class="separator separator-dashed my-10"></div>
    <div class="d-flex flex-stack">
        <div class="d-flex text-dark">
        Developed by &nbsp;<a href="mailto:aba@aba.sa">ABA</a>
        </div>
        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
        <li class="menu-item"></li>
        </ul>
    </div>
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
</html>







