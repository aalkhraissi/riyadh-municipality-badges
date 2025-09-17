            <!--begin::Menu wrapper-->
            <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
              <!--begin::Menu-->
              <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                    <!--begin::Menu item-->
                    <div class="menu-item">
                      <a class="menu-link py-3" href="dashboard.php">
                        <span class="menu-title">الإحصائيات</span>
                      </a>
                    </div>
                    <!--end::Menu item-->
                  <!--begin::Menu item-->
                  <div class="menu-item">
                    <a class="menu-link py-3" href="list.php">
                      <span class="menu-title">قائمة الموظفين</span>
                    </a>
                  </div>
                  <!--end::Menu item-->
                 <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                 <!--begin::Menu item-->
                 <div class="menu-item">
                   <a class="menu-link py-3" href="branches.php">
                     <span class="menu-title">إدارة الفروع</span>
                   </a>
                 </div>
                 <!--end::Menu item-->
                 <!--begin::Menu item-->
                 <div class="menu-item">
                   <a class="menu-link py-3" href="users.php">
                     <span class="menu-title">إدارة المستخدمين</span>
                   </a>
                 </div>
                 <!--end::Menu item-->
                 <?php endif; ?>
              </div>
              <!--end::Menu-->
            </div>
            <!--end::Menu wrapper-->