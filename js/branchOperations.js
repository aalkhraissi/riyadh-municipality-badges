// branchOperations.js
// Contains functions for branches initialization, add, edit, delete, and event handling

// Assumes globals: branches, filteredBranches
var branches = Array.isArray(initialBranches) ? initialBranches : [];
var filteredBranches = branches;

// Utility function to escape HTML
function escapeHtml(text) {
	return $("<div>").text(text).html();
}

// Filter branches based on search input
function filterBranches() {
	var query = $("#searchBranchInput").val().toLowerCase();
	filteredBranches = branches.filter(function (item) {
		return item.name && item.name.toLowerCase().includes(query);
	});
}

// Render branches table
function renderBranchesTable() {
	console.log(
		"renderBranchesTable called with",
		filteredBranches.length,
		"branches",
	);
	$("#branchesTable tbody").empty();

	if (filteredBranches.length === 0) {
		$("#branchesTable tbody").html(
			`<tr>
	               <td colspan="2" class="text-center">لا توجد فروع</td>
	           </tr>`,
		);
	} else {
		$.each(filteredBranches, function (i, item) {
			$("#branchesTable tbody").append(
				`<tr data-id="${item.id}">
	                   <td class="text-center" style="vertical-align: middle;">
					   ${escapeHtml(item.name)}</td>
	                   <td class="text-center">
	                       <div class="d-flex gap-1 justify-content-center">
	                           <button class="btn btn-sm btn-icon btn-light  btn-active-light-primary editBranchBtn"
	                                   type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="تعديل"
	                                   data-id="${item.id}">
	                               <i class="ki-duotone ki-notepad-edit text-primary fs-2">
	                                   <span class="path1"></span>
	                                   <span class="path2"></span>
	                               </i>
	                           </button>
	                           <button class="btn btn-sm btn-icon btn-light btn-active-light-danger deleteBranchBtn"
	                                   type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف"
	                                   data-id="${item.id}">
	                               <i class="ki-duotone ki-trash text-danger fs-2">
	                                   <span class="path1"></span>
	                                   <span class="path2"></span>
	                                   <span class="path3"></span>
	                                   <span class="path4"></span>
	                                   <span class="path5"></span>
	                               </i>
	                           </button>
	                       </div>
	                   </td>
	               </tr>`,
			);
		});
	}
}

// Initialize the branches table and event handlers
function initializeBranches() {
	filterBranches();
	renderBranchesTable();
}

// Add branch button click handler
$(document).on("click", ".addBranchBtn", function () {
	$("#addBranchModal").show();
});

// Cancel adding branch
$("#cancelAddBranch").click(function () {
	$("#addBranchModal").hide();
});

// Handle form submission to add a new branch
$("#addBranchForm").submit(function (e) {
	e.preventDefault();
	var name = $("#addBranchName").val().trim();

	$.post(
		"branch_data.php",
		{
			action: "add",
			name: name,
		},
		function (response) {
			if (response.status === "success") {
				branches.push(response.entry);
				$("#addBranchModal").hide();
				$("#addBranchForm")[0].reset();
				filterBranches();
				renderBranchesTable();
			} else {
				alert("Failed to add branch.");
			}
		},
		"json",
	);
});

// Handle clicking the "Edit" button for branches
$(document).on("click", ".editBranchBtn", function () {
	var id = $(this).data("id");
	var branch = branches.find(function (item) {
		return item.id == id;
	});

	if (branch) {
		$("#editBranchId").val(branch.id);
		$("#editBranchName").val(branch.name);
		$("#editBranchModal").show();
	}
});

// Save edited branch
$("#editBranchForm").submit(function (e) {
	e.preventDefault();
	var id = $("#editBranchId").val();
	var name = $("#editBranchName").val().trim();

	$.post(
		"branch_data.php",
		{
			action: "edit",
			id: id,
			name: name,
		},
		function (response) {
			if (response.status === "success") {
				// Update local data
				$.each(branches, function (i, item) {
					if (item.id == id) {
						item.name = name;
					}
				});
				filterBranches();
				renderBranchesTable();
				$("#editBranchModal").hide();
			} else {
				alert("Failed to update branch.");
			}
		},
		"json",
	);
});

// Cancel editing branch
$("#cancelEditBranch").click(function () {
	$("#editBranchModal").hide();
});

// Handle delete branch button
$(document).on("click", ".deleteBranchBtn", function () {
	var id = $(this).data("id");
	if (confirm("هل أنت متأكد من حذف هذا الفرع؟")) {
		$.post(
			"branch_data.php",
			{
				action: "delete",
				id: id,
			},
			function (response) {
				if (response.status === "success") {
					branches = branches.filter(function (item) {
						return item.id != id;
					});
					filterBranches();
					renderBranchesTable();
				} else {
					alert("Failed to delete branch.");
				}
			},
			"json",
		);
	}
});

// Handle search input for branches
$("#searchBranchInput").on("input", function () {
	filterBranches();
	renderBranchesTable();
});
