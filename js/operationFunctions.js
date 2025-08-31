// operationFunctions.js
// Contains functions for initialization, add, edit, delete, and event handling

// Initialize the table, max number, and event handlers
function initialize() {
	updateMaxNumber();
	setNextNumber();
	filterData();
	renderTable(); // to display the data
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
	var numberVal = maxNumber + 1;
	var name = $("#addName").val().trim();
	var email = $("#addEmail").val().trim();
	var department = $("#addDepartment").val().trim();
	var administration = $("#addPosition").val().trim();

	$.post(
		"data.php",
		{
			action: "add",
			number: numberVal,
			name: name,
			email: email,
			department: department,
			administration: administration,
		},
		function (response) {
			if (response.status === "success") {
				data.push(response.entry);
				maxNumber = numberVal;
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
	var administration = row.find("td").eq(3).text();
	var department = row.find("td").eq(4).text();
	var email = row.find("td").eq(5).text();

	$("#editId").val(id);
	$("#editName").val(name);
	$("#editEmail").val(email);
	$("#editDepartment").val(department);
	$("#editPosition").val(administration);
	$("#editModal").show();
});

// Save edited record
$("#editForm").submit(function (e) {
	e.preventDefault();
	var id = $("#editId").val();
	var name = $("#editName").val().trim();
	var email = $("#editEmail").val().trim();
	var department = $("#editDepartment").val().trim();
	var administration = $("#editPosition").val().trim();

	$.post(
		"data.php",
		{
			action: "edit",
			id: id,
			name: name,
			department: department,
			administration: administration,
			email: email,
		},
		function (response) {
			if (response.status === "success") {
				// update local data
				$.each(data, function (i, item) {
					if (item.id === id) {
						item.name = name;
						item.department = department;
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
	window.location.href = "export_csv.php";
});

$(document).on("change", "#csvFileInput", function () {
	if (!this.files || !this.files[0]) return;

	// Show loading modal with initial progress
	showImportProgress("Importing CSV file...", 25);

	var formData = new FormData();
	formData.append("csvfile", this.files[0]);

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
		success: function (response) {
			clearInterval(progressInterval);
			// jQuery automatically parses JSON responses, so response is already an object
			var result = response;
			updateImportProgress(result.message, 100);

			if (result.status === "success" || result.status === "no_data") {
				console.log("Import successful, reloading data...");
				// Reload data from server to update the table
				$.ajax({
					url: "data.php",
					type: "GET",
					dataType: "json",
					success: function (newData) {
						console.log("Raw response from data.php:", newData);
						console.log("Response type:", typeof newData);
						console.log("Is array:", Array.isArray(newData));
						// Update global data variable
						data = Array.isArray(newData) ? newData : [];
						filteredData = data;
						currentPage = 1;

						console.log("Data updated, records:", data.length);

						// Update UI
						updateMaxNumber();
						setNextNumber();
						filterDataKeepPage();
						renderTable();

						console.log("Table rendered, hiding progress");
						setTimeout(function () {
							hideImportProgress();
						}, 1500);
					},
					error: function (xhr, status, error) {
						console.error("Failed to reload data after import:", status, error);
						console.error("Response text:", xhr.responseText);
						hideImportProgress();
					},
				});
			} else {
				console.log("Import failed or no data");
				setTimeout(function () {
					hideImportProgress();
				}, 2000);
			}
		},
		error: function (xhr, status, error) {
			clearInterval(progressInterval);
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
