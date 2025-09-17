// readOperations.js
// Contains functions for reading/filtering/displaying data

// Assumes globals: data, filteredData, currentPage, maxNumber
var data = Array.isArray(initialData) ? initialData : [];
var filteredData = data;
var currentPage = 1;
var rowsPerPage = 10;
var selectedBranch = "";

var maxNumber = 0;

// Update maxNumber according to current data
function updateMaxNumber() {
	maxNumber = 0;
	$.each(data, function (i, item) {
		if (item.number && !isNaN(item.number)) {
			var num = parseInt(item.number, 10);
			if (num > maxNumber) {
				maxNumber = num;
			}
		}
	});
}

// Utility to pad number with zeros
function padZero(num, length) {
	return num.toString().padStart(length, "0");
}

// Set the next number in the add form input based on selected branch
function setNextNumber() {
	var branchId = $("#branchFilter").val();
	if (branchId) {
		// Get max number for selected branch via AJAX
		$.get(
			"data.php",
			{
				action: "get_max_number",
				branch_id: branchId,
			},
			function (response) {
				var nextNumber = (response.max_number || 0) + 1;
				$("#addNumber").val(padZero(nextNumber, 3));
			},
			"json",
		);
	} else {
		// Fallback to global max number
		$("#addNumber").val(padZero(maxNumber + 1, 3));
	}
}

// Utility function to escape HTML
function escapeHtml(text) {
	return $("<div>").text(text).html();
}

// Filter data based on search input and branch
function filterData() {
	var query = $("#searchInput").val().toLowerCase();
	selectedBranch = $("#branchFilter").val();

	console.log("Filtering data - Query:", query, "Branch:", selectedBranch);
	console.log("Total data records:", data.length);

	filteredData = data.filter(function (item) {
		var matchesSearch =
			(item.name && item.name.toLowerCase().includes(query)) ||
			(item.number && item.number.toString().includes(query)) ||
			(item.department && item.department.toLowerCase().includes(query)) ||
			(item.general_administration &&
				item.general_administration.toLowerCase().includes(query)) ||
			(item.administration &&
				item.administration.toLowerCase().includes(query));

		// Branch filtering logic
		var matchesBranch = true; // Default to true (show all)
		if (selectedBranch && selectedBranch !== "") {
			// If a specific branch is selected, filter by that branch
			matchesBranch = item.branch_id == selectedBranch;
			console.log(
				"Checking branch match - Item branch_id:",
				item.branch_id,
				"Selected branch:",
				selectedBranch,
				"Matches:",
				matchesBranch,
			);
		}
		// If selectedBranch is empty, show all records (matchesBranch remains true)

		var finalMatch = matchesSearch && matchesBranch;
		if (finalMatch) {
			console.log("Record matches:", item.name, "Branch:", item.branch_id);
		}

		return finalMatch;
	});

	console.log("Filtered data count:", filteredData.length);
	currentPage = 1;
}

// Filter data without resetting page
function filterDataKeepPage() {
	var query = $("#searchInput").val().toLowerCase();
	selectedBranch = $("#branchFilter").val();
	filteredData = data.filter(function (item) {
		var matchesSearch =
			(item.name && item.name.toLowerCase().includes(query)) ||
			(item.number && item.number.toString().includes(query)) ||
			(item.department && item.department.toLowerCase().includes(query)) ||
			(item.general_administration &&
				item.general_administration.toLowerCase().includes(query)) ||
			(item.administration &&
				item.administration.toLowerCase().includes(query));

		// Branch filtering logic
		var matchesBranch = true; // Default to true (show all)
		if (selectedBranch && selectedBranch !== "") {
			// If a specific branch is selected, filter by that branch
			matchesBranch = item.branch_id == selectedBranch;
		}
		// If selectedBranch is empty, show all records (matchesBranch remains true)

		return matchesSearch && matchesBranch;
	});
	// Keep current page
}
// Render table with current page and filter
function renderTable() {
	console.log(
		"Rendering table - Current page:",
		currentPage,
		"Rows per page:",
		rowsPerPage,
		"Filtered data:",
		filteredData.length,
	);
	// Clear existing table content
	$("#dataTable tbody").empty();
	$("#noDataMessage").remove();

	var startIdx = (currentPage - 1) * rowsPerPage;
	var endIdx = Math.min(startIdx + rowsPerPage, filteredData.length);
	var displaySlice = filteredData.slice(startIdx, endIdx);

	if (displaySlice.length === 0) {
		$("#dataTable tbody").html(
			`<tr id="noDataMessage" class="text-center">
		              <td colspan="8">لا يوجد معلومات في هذا الجدول</td>
		          </tr>`,
		);
	} else {
		$.each(displaySlice, function (i, item) {
			$("#dataTable tbody").append(
				`<tr data-id="${item.id}">
			<td class="text-center" style="vertical-align: middle;">
			<div class="form-check form-check-sm form-check-custom form-check-solid d-inline-block">
		          	<input class="form-check-input checkbox" type="checkbox"
				data-id="${item.id}" />
		          </div>
			</td>
			<td class="text-center" style="vertical-align: middle;">
			${escapeHtml(padZero(item.number, 3))}</td>
			       <td class="text-center" style="vertical-align: middle;">
				   ${escapeHtml(item.name)}</td>
			      <td class="text-center" style="vertical-align: middle;">
				  ${escapeHtml(item.general_administration || "")}</td>
			      <td class="text-center" style="vertical-align: middle;">
				  ${escapeHtml(item.administration)}</td>
			             <td class="text-center" style="vertical-align: middle;">
						 ${escapeHtml(item.department || "")}</td>
			             <td class="text-center" style="vertical-align: middle;">
						 ${escapeHtml(item.email)}</td>
		        <td class="text-center">
		            <div class="dropdown">
		                <button class="btn btn-sm btn-icon btn-light btn-active-light-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
		                 <i class="ki-duotone ki-category text-primary fs-1">
							<span class="path1"></span>
							<span class="path2"></span>
							<span class="path3"></span>
							<span class="path4"></span>
						</i>
		                </button>
		                <ul class="dropdown-menu w-150px">
		                    <li class="text-start w-100">
		                        <a class="dropdown-item d-flex align-items-center w-100 downloadBtn" href="#" 
								data-id="${item.id}">
		                            <i class="ki-duotone ki-scan-barcode fs-2 me-5">
		                                <span class="path1"></span>
		                                <span class="path2"></span>
		                                <span class="path3"></span>
		                                <span class="path4"></span>
		                                <span class="path5"></span>
		                                <span class="path6"></span>
		                                <span class="path7"></span>
		                                <span class="path8"></span>
		                            </i>
		                            تحميل رمز QR
		                        </a>
		                    </li>
		                    <li class="text-start">
		                        <a class="dropdown-item w-100 d-flex align-items-center previewBtn" href="#"
								data-id="${item.id}">
		                            <i class="ki-duotone ki-eye fs-2 text-info me-5">
		                                <span class="path1"></span>
		                                <span class="path2"></span>
		                                <span class="path3"></span>
		                            </i>
		                            معاينة
		                        </a>
		                    </li>
		                    <li class="text-start">
		                        <a class="dropdown-item w-100 d-flex align-items-center editBtn" href="#" 
								data-id="${item.id}">
		                            <i class="ki-duotone ki-notepad-edit text-warning fs-2 me-5">
		                                <span class="path1"></span>
		                                <span class="path2"></span>
		                            </i>
		                            تعديل
		                        </a>
		                    </li>
		                    <li><hr class="dropdown-divider"></li>
		                    <li class="text-start">
		                        <a class="dropdown-item d-flex align-items-center w-100 text-danger deleteBtn" href="#" 
								data-id="${item.id}">
		                            <i class="ki-duotone ki-trash text-danger fs-2 me-5">
		                                <span class="path1"></span>
		                                <span class="path2"></span>
		                                <span class="path3"></span>
		                                <span class="path4"></span>
		                                <span class="path5"></span>
		                            </i>
		                            حذف
		                        </a>
		                    </li>
		                </ul>
		            </div>
		          </td>
		        </tr>`,
			);
		});
	}
	updatePageInfo();
}

// Update pagination info and button states
function updatePageInfo() {
	var totalPages = Math.ceil(filteredData.length / rowsPerPage) || 1;

	// Calculate records information
	var totalRecords = filteredData.length;
	var recordsInfoText = "";

	if (rowsPerPage >= totalRecords) {
		// Show all records
		recordsInfoText = "عرض الكل من " + totalRecords + " سجل";
	} else {
		var startRecord = (currentPage - 1) * rowsPerPage + 1;
		var endRecord = Math.min(currentPage * rowsPerPage, filteredData.length);
		recordsInfoText =
			"عرض " +
			startRecord +
			" إلى " +
			endRecord +
			" من " +
			totalRecords +
			" سجل";
	}

	// Update records information
	$("#recordsInfo").text(recordsInfoText);

	// Clear existing page number buttons
	$("#pageNumbers").remove();

	// Hide pagination controls when showing all records
	if (rowsPerPage >= totalRecords) {
		$("#paginationContainer").hide();
		return;
	} else {
		$("#paginationContainer").show();
	}

	// Create container for page numbers
	var pageNumbersContainer = $(
		'<li id="pageNumbers" class="d-flex align-items-center mx-2"></li>',
	);

	// Generate page number buttons (show up to 5 pages around current page)
	var startPage = Math.max(1, currentPage - 2);
	var endPage = Math.min(totalPages, currentPage + 2);

	// If we're near the beginning, show more pages at the end
	if (endPage - startPage < 4) {
		endPage = Math.min(totalPages, startPage + 4);
	}

	// If we're near the end, show more pages at the beginning
	if (endPage - startPage < 4) {
		startPage = Math.max(1, endPage - 4);
	}

	// Add page number buttons
	for (var i = startPage; i <= endPage; i++) {
		var pageButton = $(
			` <li class="page-item ${
				i === currentPage ? "active" : ""
			}"><a href="#" class="page-link mx-1">${i}</a></li>`,
		);
		// Use a closure to capture the page number
		(function (pageNum) {
			pageButton.click(function (e) {
				e.preventDefault();
				currentPage = pageNum;
				renderTable();
			});
		})(i);
		pageNumbersContainer.append(pageButton);
	}

	// Insert page numbers between prev and next buttons
	$("#prevPage").after(pageNumbersContainer);

	// Update first page button
	if (currentPage <= 1) {
		$("#firstPage").addClass("disabled");
		$("#prevPage").addClass("disabled");
	} else {
		$("#firstPage").removeClass("disabled");
		$("#prevPage").removeClass("disabled");
	}

	// Update last page button
	if (currentPage >= totalPages) {
		$("#nextPage").addClass("disabled");
		$("#lastPage").addClass("disabled");
	} else {
		$("#nextPage").removeClass("disabled");
		$("#lastPage").removeClass("disabled");
	}
}

$("#prevPage").click(function () {
	if (currentPage > 1) {
		currentPage--;
		renderTable();
	}
});

$("#nextPage").click(function () {
	var totalPages = Math.ceil(filteredData.length / rowsPerPage);
	if (currentPage < totalPages) {
		currentPage++;
		renderTable();
	}
});

// Handle first page button click
$("#firstPage").click(function () {
	if (currentPage > 1) {
		currentPage = 1;
		renderTable();
	}
});

// Handle last page button click
$("#lastPage").click(function () {
	var totalPages = Math.ceil(filteredData.length / rowsPerPage);
	if (currentPage < totalPages) {
		currentPage = totalPages;
		renderTable();
	}
});

// Handle rows per page change
$(document).on("change", "#rowsPerPage", function () {
	console.log("Rows per page changed to:", $(this).val());
	var newRowsPerPage = $(this).val();

	// Update select2 display
	$("#rowsPerPage").trigger("change.select2");

	if (newRowsPerPage === "all") {
		// Show all records
		rowsPerPage = filteredData.length;
		currentPage = 1;
		console.log("Setting rows per page to ALL:", rowsPerPage);
		renderTable();
	} else {
		var rows = parseInt(newRowsPerPage);
		if (!isNaN(rows) && rows > 0) {
			rowsPerPage = rows;
			currentPage = 1;
			console.log("Setting rows per page to:", rowsPerPage);
			renderTable();
		} else {
			console.log("Invalid rows per page value:", newRowsPerPage);
		}
	}
});
