// readOperations.js
// Contains functions for reading/filtering/displaying data

// Assumes globals: data, filteredData, currentPage, maxNumber
var data = Array.isArray(initialData) ? initialData : [];
var filteredData = data;
var currentPage = 1;
var rowsPerPage = 10;

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

// Set the next number in the add form input
function setNextNumber() {
	$("#addNumber").val(padZero(maxNumber + 1, 3));
}

// Utility function to escape HTML
function escapeHtml(text) {
	return $("<div>").text(text).html();
}

// Filter data based on search input
function filterData() {
	var query = $("#searchInput").val().toLowerCase();
	filteredData = data.filter(function (item) {
		return (
			(item.name && item.name.toLowerCase().includes(query)) ||
			(item.number && item.number.toString().includes(query))
		);
	});
	currentPage = 1;
}

// Filter data without resetting page
function filterDataKeepPage() {
	var query = $("#searchInput").val().toLowerCase();
	filteredData = data.filter(function (item) {
		return (
			(item.name && item.name.toLowerCase().includes(query)) ||
			(item.number && item.number.toString().includes(query))
		);
	});
	// Keep current page
}
// Render table with current page and filter
function renderTable() {
	$("#dataTable tbody").empty();

	var startIdx = (currentPage - 1) * rowsPerPage;
	var endIdx = Math.min(startIdx + rowsPerPage, filteredData.length);
	var displaySlice = filteredData.slice(startIdx, endIdx);

	if (displaySlice.length === 0) {
		$("#dataTable tbody").html(
			`<tr id="noDataMessage" class="text-center">
                <td colspan="5">لا يوجد معلومات في هذا الجدول</td>
            </tr>`,
		);
	} else {
		$.each(displaySlice, function (i, item) {
			$("#dataTable tbody").append(
				`<tr data-id="${item.id}">
			<td class="text-center">${escapeHtml(padZero(item.number, 3))}</td>
            <td class="text-center">${escapeHtml(item.name)}</td>
			<td class="text-center">${escapeHtml(item.position)}</td>
            <td class="text-center">${escapeHtml(item.email)}</td>
            <td class="text-center">
			 <button class="btn btn-icon btn-sm btn-secondary downloadBtn" data-id="${
					item.id
				}">
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
              <button class="btn btn-icon btn-sm btn-light-info previewBtn">
				<i class="ki-duotone ki-eye fs-1">
				<span class="path1"></span>
				<span class="path2"></span>
				<span class="path3"></span>
				</i>
			  </button>
              <button class="btn btn-icon btn-sm btn-light-warning editBtn">
				<i class="ki-duotone ki-notepad-edit fs-1">
				<span class="path1"></span>
				<span class="path2"></span>
				</i>
			  </button>
              <button class="btn btn-icon btn-sm btn-light-danger deleteBtn">
				<i class="ki-duotone ki-trash fs-1">
				<span class="path1"></span>
				<span class="path2"></span>
				<span class="path3"></span>
				<span class="path4"></span>
				<span class="path5"></span>
				</i>
			  </button>
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
	$("#pageInfo").text(`${currentPage} / ${totalPages}`);

	if (currentPage <= 1) {
		document.getElementById("prevPage").classList.add("disabled");
	} else {
		document.getElementById("prevPage").classList.remove("disabled");
	}

	if (currentPage >= totalPages) {
		document.getElementById("nextPage").classList.add("disabled");
	} else {
		document.getElementById("nextPage").classList.remove("disabled");
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

// Handle rows per page change
$("#rowsPerPage").change(function () {
	var newRowsPerPage = parseInt($(this).val());
	if (!isNaN(newRowsPerPage) && newRowsPerPage > 0) {
		rowsPerPage = newRowsPerPage;
		currentPage = 1;
		renderTable();
	}
});
