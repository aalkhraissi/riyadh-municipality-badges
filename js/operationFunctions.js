// operationFunctions.js
// Contains functions for initialization, add, edit, delete, and event handling

// Initialize the table, max number, and event handlers
function initialize() {
	console.log("Initializing table with", data.length, "records");
	console.log("Initial data sample:", data.slice(0, 3));
	console.log("Setting up event handlers...");

	// Ensure rows per page handler is attached
	$(document)
		.off("change", "#rowsPerPage")
		.on("change", "#rowsPerPage", function () {
			console.log("Rows per page changed (initialize handler):", $(this).val());
			var newRowsPerPage = $(this).val();

			// Update select2 display
			$("#rowsPerPage").trigger("change.select2");

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

	updateMaxNumber();
	filterData();
	renderTable(); // to display the data
	// Set next number after a short delay to ensure branch filter is ready
	setTimeout(setNextNumber, 100);
}

// Add button click handler
$(document).on("click", ".addBtn", function () {
	setNextNumber();
	$("#addModal").show();
});

// Cancel adding
$("#cancelAdd").click(function () {
	$("#addModal").hide();
});

// Handle form submission to add a new record
$("#addForm").submit(function (e) {
	e.preventDefault();
	var name = $("#addName").val().trim();
	var email = $("#addEmail").val().trim().toLowerCase();
	var department = $("#addDepartment").val().trim();
	var administration = $("#addAdministration").val().trim();
	var generalAdministration = $("#addGeneralAdministration").val().trim();
	var branchId = $("#branchFilter").val();

	$.post(
		"data.php",
		{
			action: "add",
			number: 0, // Server will calculate the correct branch-specific number
			name: name,
			email: email,
			department: department,
			general_administration: generalAdministration,
			administration: administration,
			branch_id: branchId,
		},
		function (response) {
			if (response.status === "success") {
				data.push(response.entry);
				// Update maxNumber for the current branch
				if (branchId && response.entry.branch_id == branchId) {
					maxNumber = Math.max(maxNumber, response.entry.number);
				}
				$("#addModal").hide();
				$("#addForm")[0].reset();
				setNextNumber();
				filterDataKeepPage();
				renderTable();
			} else {
				alert("Failed to add record.");
			}
		},
		"json",
	);
});

// Handle clicking the "Edit" button
$(document).on("click", ".editBtn", function () {
	var row = $(this).closest("tr");
	var id = row.data("id");
	var name = row.find("td").eq(2).text();
	var generalAdministration = row.find("td").eq(3).text();
	var administration = row.find("td").eq(4).text();
	var department = row.find("td").eq(5).text();
	var email = row.find("td").eq(6).text();

	$("#editId").val(id);
	$("#editName").val(name);
	$("#editEmail").val(email);
	$("#editDepartment").val(department);
	$("#editGeneralAdministration").val(generalAdministration);
	$("#editAdministration").val(administration);
	$("#editModal").show();
});

// Save edited record
$("#editForm").submit(function (e) {
	e.preventDefault();
	var id = $("#editId").val();
	var name = $("#editName").val().trim();
	var email = $("#editEmail").val().trim().toLowerCase();
	var department = $("#editDepartment").val().trim();
	var generalAdministration = $("#editGeneralAdministration").val().trim();
	var administration = $("#editAdministration").val().trim();
	var branchId = $("#branchFilter").val();

	$.post(
		"data.php",
		{
			action: "edit",
			id: id,
			name: name,
			department: department,
			general_administration: generalAdministration,
			administration: administration,
			email: email,
			branch_id: branchId,
		},
		function (response) {
			if (response.status === "success") {
				// update local data
				$.each(data, function (i, item) {
					if (item.id === id) {
						item.name = name;
						item.department = department;
						item.general_administration = generalAdministration;
						item.administration = administration;
						item.email = email;
					}
				});
				filterDataKeepPage();
				renderTable();
				$("#editModal").hide();
			} else {
				alert("Failed to update.");
			}
		},
		"json",
	);
});

// Cancel editing
$("#cancelEdit").click(function () {
	$("#editModal").hide();
});

// Handle checkbox selection
$(document).on("change", ".checkbox", function () {
	updateDownloadButton();
});

// Handle select all checkbox
$(document).on("change", "#selectAll", function () {
	var checked = $(this).prop("checked");
	$(".checkbox").prop("checked", checked);
	updateDownloadButton();
});

// Update download button state based on selection
function updateDownloadButton() {
	var selectedCount = $(".checkbox:checked").length;
	var totalCount = $(".checkbox").length;

	if (selectedCount > 0) {
		$("#downloadSelectedBtn").prop("disabled", false);
	} else {
		$("#downloadSelectedBtn").prop("disabled", true);
	}

	// Update select all checkbox state
	if (selectedCount === totalCount && totalCount > 0) {
		$("#selectAll").prop("checked", true);
	} else {
		$("#selectAll").prop("checked", false);
	}
}

// Handle download selected QR codes button
$(document).on("click", "#downloadSelectedBtn", function () {
	var selectedIds = [];
	$(".checkbox:checked").each(function () {
		selectedIds.push($(this).data("id"));
	});

	if (selectedIds.length > 0) {
		// Redirect to PHP script to generate and download multiple QR codes
		window.location.href =
			"download_multiple_qr.php?ids=" +
			encodeURIComponent(selectedIds.join(","));
	}
});

$(document).on("click", ".downloadBtn", function () {
	var id = $(this).data("id");
	// Redirect to PHP script to generate and download QR
	window.location.href = "download_qr.php?id=" + encodeURIComponent(id);
});

$(document).on("click", ".previewBtn", function () {
	var row = $(this).closest("tr");
	var id = row.data("id");

	// Redirect to preview page
	window.open("preview.php?id=" + encodeURIComponent(id), "_blank");
});

// Handle delete button
$(document).on("click", ".deleteBtn", function () {
	var row = $(this).closest("tr");
	Swal.fire({
		title: "هل انت متأكد؟؟",
		text: "لا يمكن استعادة المعلومات المحذوفة",
		icon: "error",
		showCancelButton: true,
		confirmButtonText: "نعم، قم بعملية الحذف",
		cancelButtonText: "لا ارغب بالحذف",
		reverseButtons: true,
		customClass: {
			confirmButton: "btn btn-light-danger btn-sm rounded",
			cancelButton: "btn btn-secondary btn-sm rounded",
		},
	}).then(async function (result) {
		if (result.value) {
			var id = row.data("id");

			$.post(
				"data.php",
				{
					action: "delete",
					id: id,
				},
				function (response) {
					if (response.status === "success") {
						data = data.filter(function (item) {
							return item.id !== id;
						});
						filterDataKeepPage();
						renderTable();
					} else {
						alert("Failed to delete record.");
					}
				},
				"json",
			);
		} else if (result.dismiss === "cancel") {
			return false;
		}
	});
});

// Handle export button
$(document).on("click", "#exportBtn", function () {
	var branchId = $("#branchFilter").val();
	var url = "export_csv.php";
	if (branchId) {
		url += "?branch_id=" + encodeURIComponent(branchId);
	}
	window.location.href = url;
});

// Handle label click to ensure file input is triggered
$(document).on("click", "label[for='csvFileInput']", function (e) {
	console.log("Import button clicked");
	$("#csvFileInput").click();
});

$(document).on("change", "#csvFileInput", function () {
	console.log("File input changed, files:", this.files);
	if (!this.files || !this.files[0]) {
		console.log("No file selected");
		return;
	}

	console.log(
		"Selected file:",
		this.files[0].name,
		"Size:",
		this.files[0].size,
	);

	// Show loading modal with initial progress
	showImportProgress("Importing CSV file...", 25);

	var formData = new FormData();
	formData.append("csvfile", this.files[0]);
	var branchId = $("#branchFilter").val();
	console.log("Branch filter value:", branchId);
	console.log("Branch filter type:", typeof branchId);

	if (branchId && branchId !== "") {
		formData.append("branch_id", branchId);
		console.log("Importing to branch ID:", branchId);
	} else {
		console.log("Importing without branch selection (all branches)");
		formData.append("branch_id", ""); // Send empty string to indicate no branch
	}

	// Simulate progress updates
	var progressInterval = setInterval(function () {
		var currentWidth = parseInt($("#importProgressBar").css("width"));
		if (currentWidth < 90) {
			updateImportProgress(
				"Processing records...",
				Math.min(currentWidth + 15, 90),
			);
		}
	}, 300);

	$.ajax({
		url: "import_csv.php",
		type: "POST",
		data: formData,
		processData: false,
		contentType: false,
		success: function (response, textStatus, xhr) {
			clearInterval(progressInterval);
			console.log("AJAX Success - Response:", response);
			console.log("Response type:", typeof response);
			console.log("Response headers:", xhr.getAllResponseHeaders());

			// jQuery automatically parses JSON responses, so response is already an object
			var result = response;
			updateImportProgress(result.message, 100);

			if (result.status === "success" || result.status === "no_data") {
				console.log("Import successful, reloading page...");
				console.log("Imported count:", result.importedCount);
				console.log("Total rows:", result.totalRows);
				// Simply reload the page to ensure all data is refreshed
				setTimeout(function () {
					location.reload();
				}, 1500);
			} else {
				console.log("Import failed or no data");
				console.log("Status:", result.status);
				console.log("Errors:", result.errors);
				setTimeout(function () {
					hideImportProgress();
				}, 2000);
			}
		},
		error: function (xhr, status, error) {
			clearInterval(progressInterval);
			console.log("AJAX Error - Status:", status);
			console.log("Error:", error);
			console.log("XHR response:", xhr.responseText);
			console.log("XHR status:", xhr.status);
			updateImportProgress("Import failed: " + error, 0);
			setTimeout(function () {
				hideImportProgress();
			}, 2000);
		},
	});
});

// Handle search input
$("#searchInput").on("input", function () {
	var query = $(this).val().toLowerCase();
	filteredData = data.filter(function (item) {
		return (
			item.name.toLowerCase().includes(query) ||
			item.number.toString().includes(query) ||
			(item.department && item.department.toLowerCase().includes(query)) ||
			(item.general_administration &&
				item.general_administration.toLowerCase().includes(query)) ||
			(item.administration && item.administration.toLowerCase().includes(query))
		);
	});
	currentPage = 1;
	renderTable();
});

// Import progress functions
function showImportProgress(message, percentage = 0) {
	$("#importProgressModal").modal("show");
	$("#importProgressText").text(message);
	$("#importProgressBar").css("width", percentage + "%");
}

function updateImportProgress(message, percentage) {
	$("#importProgressText").text(message);
	$("#importProgressBar").css("width", percentage + "%");
}

function hideImportProgress() {
	setTimeout(function () {
		$("#importProgressModal").modal("hide");
	}, 1000); // Keep visible for 1 second after completion
}
