<!--begin::Toolbar-->
<div id="kt_app_toolbar" class="app-toolbar  py-3 py-lg-6 ">
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container  container-xxl d-flex flex-stack ">
    <!--begin::Page title-->
    <div class="page-title d-flex align-items-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 align-items-center my-0">
        <?php echo isset($page_title) ? $page_title : 'لوحة التحكم'; ?>
        </h1>
        <!--end::Title-->
        <!--begin::Separator-->
        <span class="h-20px border-gray-300 border-start mx-4"></span>
        <!--end::Separator-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="dashboard.php" class="text-muted text-hover-primary">
            الرئيسية
            </a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <?php echo isset($page_title) ? $page_title : 'لوحة التحكم'; ?>
        </li>
        <!--end::Item-->
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
    <!--begin::Actions-->
    <div class="w-100 w-lg-500px d-flex flex-end align-items-center">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);

        // Dashboard page actions
        if ($current_page === 'dashboard.php') {
        ?>
            <a href="list.php" class="btn btn-sm btn-flex btn-light btn-active-primary fw-bold">
                <i class="ki-duotone ki-element-11 fs-5 text-muted me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                    <span class="path6"></span>
                </i>
                عرض القائمة
            </a>
        <?php
        }

        // List page actions
        elseif ($current_page === 'list.php') {
        ?>
            <button type="button" class="btn btn-sm btn-flex btn-light btn-active-primary fw-bold addBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="إضافة موظف جديد">
                <i class="ki-duotone ki-plus fs-5 me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                إضافة موظف
            </button>
            <button type="button" class="btn btn-sm btn-flex btn-light btn-active-success fw-bold ms-5" id="exportBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="تصدير البيانات">
                <i class="ki-duotone ki-cloud-download fs-5 me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                تصدير
            </button>

            <form id="importForm" enctype="multipart/form-data" style="display: inline;">
                <label for="csvFileInput" class="btn btn-sm btn-flex btn-light btn-active-info fw-bold mx-5" data-bs-toggle="tooltip" data-bs-placement="bottom" title="استيراد البيانات">
                    <i class="ki-duotone ki-cloud-add fs-5 me-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    استيراد
                </label>
                <input type="file" id="csvFileInput" name="csvfile" accept=".csv" style="display:none;" />
            </form>

            <button type="button" class="btn btn-sm btn-flex btn-light btn-active-dark fw-bold" id="downloadSelectedBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="تحميل رموز QR المحددة">
                <i class="ki-duotone ki-scan-barcode fs-5 me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                    <span class="path6"></span>
                    <span class="path7"></span>
                    <span class="path8"></span>
                </i>
                تحميل QR code
            </button>

        <?php
        }

        // Users page actions
        elseif ($current_page === 'users.php') {
        ?>
            <button type="button" class="btn btn-sm btn-flex btn-light btn-active-primary fw-bold addUserBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="إضافة مستخدم جديد">
                <i class="ki-duotone ki-plus fs-5 text-muted me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                إضافة مستخدم
            </button>
        <?php
        }

        // Branches page actions
        elseif ($current_page === 'branches.php') {
        ?>
            <button type="button" class="btn btn-sm btn-flex btn-light btn-active-primary fw-bold addBranchBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="إضافة فرع جديد">
                <i class="ki-duotone ki-plus fs-5 text-muted me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                إضافة فرع
            </button>
        <?php
        }
        ?>
    </div>
    <!--end::Actions-->
    </div>
    <!--end::Toolbar container-->
</div>
<!--end::Toolbar-->