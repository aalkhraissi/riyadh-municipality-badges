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
	var position = $("#addPosition").val().trim();

	$.post(
		"data.php",
		{
			action: "add",
			number: numberVal,
			name: name,
			email: email,
			position: position,
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
	var name = row.find("td").eq(1).text();
	var position = row.find("td").eq(2).text();
	var email = row.find("td").eq(3).text();

	$("#editId").val(id);
	$("#editName").val(name);
	$("#editEmail").val(email);
	$("#editPosition").val(position);
	$("#editModal").show();
});

// Save edited record
$("#editForm").submit(function (e) {
	e.preventDefault();
	var id = $("#editId").val();
	var name = $("#editName").val().trim();
	var email = $("#editEmail").val().trim();
	var position = $("#editPosition").val().trim();

	$.post(
		"data.php",
		{
			action: "edit",
			id: id,
			name: name,
			position: position,
			email: email,
		},
		function (response) {
			if (response.status === "success") {
				// update local data
				$.each(data, function (i, item) {
					if (item.id === id) {
						item.name = name;
						item.position = position;
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

	var formData = new FormData();
	formData.append("csvfile", this.files[0]);

	$.ajax({
		url: "import_csv.php",
		type: "POST",
		data: formData,
		processData: false,
		contentType: false,
		success: function (response) {
			alert("Import success!");
			// Reload data from server to update the table
			$.get("data.php", function (newData) {
				data = newData;
				updateMaxNumber();
				filterDataKeepPage();
				renderTable();
			});
		},
		error: function (xhr, status, error) {
			alert("Error during import: " + error);
		},
	});
});

// Handle search input
$("#searchInput").on("input", function () {
	var query = $(this).val().toLowerCase();
	filteredData = data.filter(function (item) {
		return (
			item.name.toLowerCase().includes(query) ||
			item.number.toString().includes(query)
		);
	});
	currentPage = 1;
	renderTable();
});
