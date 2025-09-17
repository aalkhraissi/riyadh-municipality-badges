            <!--begin::Navbar-->
            <div class="app-navbar flex-shrink-0">
              <div id="kt_header_search" class="d-none">
                  <div data-kt-search-element="content">
                  <input type="hidden" data-kt-search-element="input"/>
                  </div>
              </div>

              <!--begin::User menu-->
              <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                <!--begin::Menu wrapper-->
                <div class="cursor-pointer symbol symbol-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-start">
                  <img src="blank.svg" class="rounded-3" alt="user"/>
                </div>
                <!--begin::User account menu-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                  <!--begin::Menu item-->
                  <div class="menu-item px-3">
                    <div class="menu-content d-flex align-items-center px-3">
                      <!--begin::Username-->
                      <div class="d-flex flex-column">
                        <div class="fw-bold d-flex align-items-center fs-5">
                          <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'المستخدم'; ?>
                        </div>
                      </div>
                      <!--end::Username-->
                    </div>
                  </div>
                  <!--end::Menu item-->
  
                  <!--begin::Menu item-->
                  <div class="menu-item px-5">
                    <a href="logout.php" class="menu-link px-5 text-danger">
                      تسجيل الخروج
                    </a>
                  </div>
                  <!--end::Menu item-->
                </div>
                <!--end::User account menu-->
                <!--end::Menu wrapper-->
              </div>
              <!--end::User menu-->
              <!--begin::Header menu toggle-->
              <div class="app-navbar-item d-lg-none ms-2 me-n2" title="Show header menu">
                <div class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px" id="kt_app_header_menu_toggle">
                  <i class="ki-outline ki-element-4 fs-1"></i>
                </div>
              </div>
              <!--end::Header menu toggle-->
            </div>
            <!--end::Navbar-->