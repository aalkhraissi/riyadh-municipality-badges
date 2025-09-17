<?php
// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php'); // Redirect to login page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" dir="rtl" direction="rtl" style="direction:rtl;">
<!--begin::Head-->

<head>
  <base href="" />
  <title><?php echo isset($page_title) ? $page_title : 'لوحة التحكم'; ?> - Riyadh Municipality</title>
  <meta charset="utf-8" />
  <meta name="description" content="نظام إدارة موظفي بلدية الرياض" />
  <meta name="keywords" content="riyadh municipality, employee management, arabic, rtl" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="canonical" href="http://localhost/table/" />
  <link rel="shortcut icon" href="favicon.ico" />
  <!--begin::Fonts(mandatory for all pages)-->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
  <!--end::Fonts-->
  <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
  <link href="css/plugins.bundle.rtl.css" rel="stylesheet" type="text/css" />
  <link href="css/style.bundle.rtl.css" rel="stylesheet" type="text/css" />
  <link href="css/style.font.css" rel="stylesheet" type="text/css" />

  <!--end::Global Stylesheets Bundle-->
  <script>
    // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
    if (window.top != window.self) {
      window.top.location.replace(window.self.location.href);
    }
  </script>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_app_body" data-kt-app-layout="light-header" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-toolbar-enabled="true" data-kt-app-toolbar-fixed="true" data-kt-app-toolbar-fixed-mobile="true" class="app-default">
  <!--layout-partial:layout/_default.html-->
  <!--begin::App-->
  <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page  flex-column flex-column-fluid " id="kt_app_page">
      <!--layout-partial:layout/partials/_header.html-->
      <!--begin::Header-->
      <div id="kt_app_header" class="app-header " data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
        <!--begin::Header container-->
        <div class="app-container  container-xxl d-flex align-items-stretch justify-content-between " id="kt_app_header_container">
          <!--begin::Header wrapper-->
          <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">

            <?php include 'menu.php'; ?>
            <?php include 'navbar.php'; ?>

          </div>
          <!--end::Header wrapper-->
        </div>
        <!--end::Header container-->
      </div>
      <!--end::Header-->


      <!--begin::Wrapper-->
      <div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">
        <!--begin::Main-->
        <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
          <!--begin::Content wrapper-->
          <div class="d-flex flex-column flex-column-fluid">
            <!--layout-partial:layout/partials/_toolbar.html-->
            <?php include 'toolbar.php'; ?>

            <!--layout-partial:layout/partials/_content.html-->
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content  flex-column-fluid ">
              <!--begin::Content container-->
              <div id="kt_app_content_container" class="app-container  container-xxl ">

              