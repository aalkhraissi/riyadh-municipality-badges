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
    $db->createUsersTable(); // Ensure table exists
    $users = $db->getAllUsers();
    $branches = $db->getBranches();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if $users is an array
if (!is_array($users)) {
    $users = [];
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
      <div class="fv-row ms-auto w-100">
        <input type="text" id="searchUserInput" placeholder="البحث بالاسم أو اسم المستخدم" style="direction: rtl" class="form-control form-control-solid w-100">
      </div>
    </div>
       
      <!-- begin::Users Table -->
      <div class="table-responsive">
          <table id="usersTable" class="table table-rounded table-row-dashed table-row-gray-300 gy-4 gs-4" style="direction: rtl;">
          <thead>
            <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
              <th class="text-center fw-bold">اسم المستخدم</th>
              <th class="text-center fw-bold">الاسم الكامل</th>
              <th class="text-center fw-bold">نوع الوصول</th>
              <th class="text-center fw-bold">صلاحيات المدير</th>
              <th class="text-center fw-bold">تاريخ الإنشاء</th>
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

<!-- Add User modal -->
<div class="modal" id="addUserModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="addUserForm" class="form">
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addUsername" name="addUsername" required />
                <label for="addUsername">اسم المستخدم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="password" class="form-control form-control-solid" placeholder=" " value="" id="addPassword" name="addPassword" required />
                <label for="addPassword">كلمة المرور</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="text" class="form-control form-control-solid" placeholder=" " value="" id="addUserFullName" name="addUserFullName" required />
                <label for="addUserFullName">الاسم الكامل</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <select class="form-select form-select-solid" id="addBranchAccessType" name="addBranchAccessType" required>
                <option value="all_branches">جميع الفروع</option>
                <option value="specific_branches">فروع محددة</option>
              </select>
                <label for="addBranchAccessType">نوع الوصول للفروع</label>
            </div>
          </div>
          <div class="fv-row mb-5" id="specificBranchesContainer" style="display: none;">
            <label class="form-label">الفروع المحددة</label>
            <div class="row">
              <?php foreach ($branches as $branch): ?>
                <div class="col-md-6">
                  <div class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" value="<?php echo $branch['id']; ?>" id="branch_<?php echo $branch['id']; ?>" name="assignedBranches[]">
                    <label class="form-check-label" for="branch_<?php echo $branch['id']; ?>">
                      <?php echo htmlspecialchars($branch['name']); ?>
                    </label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-check form-check-custom form-check-solid">
              <input class="form-check-input" type="checkbox" value="1" id="addIsAdmin" name="addIsAdmin">
              <label class="form-check-label" for="addIsAdmin">
                مدير النظام
              </label>
            </div>
          </div>
          <div class="text-center pt-5">
            <button class="btn btn-primary w-100px ms-5" type="submit">حفظ</button>
            <button type="reset" class="btn btn-light w-100px" id="cancelAddUser">
              إلغاء
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit User modal -->
<div class="modal" id="editUserModal" style="display:none; z-index:1000; direction:rtl;">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <form id="editUserForm" class="form">
          <input type="hidden" id="editUserId" />
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="text" class="form-control form-control-solid" placeholder="اسم المستخدم" value="" id="editUsername" name="editUsername" readonly />
                <label for="editUsername">اسم المستخدم</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <input type="text" class="form-control form-control-solid" placeholder="الاسم الكامل" value="" id="editUserFullName" name="editUserFullName" required />
                <label for="editUserFullName">الاسم الكامل</label>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-floating mb-7">
              <select class="form-select form-select-solid" id="editBranchAccessType" name="editBranchAccessType" required>
                <option value="all_branches">جميع الفروع</option>
                <option value="specific_branches">فروع محددة</option>
              </select>
                <label for="editBranchAccessType">نوع الوصول للفروع</label>
            </div>
          </div>
          <div class="fv-row mb-5" id="editSpecificBranchesContainer" style="display: none;">
            <label class="form-label">الفروع المحددة</label>
            <div class="row">
              <?php foreach ($branches as $branch): ?>
                <div class="col-md-6">
                  <div class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input edit-branch-checkbox" type="checkbox" value="<?php echo $branch['id']; ?>" id="edit_branch_<?php echo $branch['id']; ?>" name="editAssignedBranches[]">
                    <label class="form-check-label" for="edit_branch_<?php echo $branch['id']; ?>">
                      <?php echo htmlspecialchars($branch['name']); ?>
                    </label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="fv-row mb-5">
            <div class="form-check form-check-custom form-check-solid">
              <input class="form-check-input" type="checkbox" value="1" id="editIsAdmin" name="editIsAdmin">
              <label class="form-check-label" for="editIsAdmin">
                مدير النظام
              </label>
            </div>
          </div>
          <div class="text-center pt-5">
            <button type="submit" class="btn btn-primary w-100px ms-5" id="saveEditUser">حفظ</button>
            <button type="reset" class="btn btn-light w-100px" id="cancelEditUser">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>

<script>
var initialUsers = <?php echo json_encode($users); ?>;
var initialBranches = <?php echo json_encode($branches); ?>;
</script>
<script src="js/jquery.js"></script>
<script src="js/userManagement.js"></script>
<script>
  $(document).ready(function() {
    initializeUsers();
  });
</script>
<?php
// Capture the content and include header/footer
$page_content = ob_get_clean();
$page_title = 'إدارة المستخدمين';
include 'layout/header.php';
echo $page_content;
include 'layout/footer.php';
?>