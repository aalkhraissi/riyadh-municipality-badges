// userManagement.js
// Contains functions for user management initialization, add, edit, delete, and event handling

// Assumes globals: users, filteredUsers, branches
var users = Array.isArray(initialUsers) ? initialUsers : [];
var filteredUsers = users;
var branches = Array.isArray(initialBranches) ? initialBranches : [];

// Utility function to escape HTML
function escapeHtml(text) {
	return $("<div>").text(text).html();
}

// Filter users based on search input
function filterUsers() {
	var query = $("#searchUserInput").val().toLowerCase();
	filteredUsers = users.filter(function (item) {
		return (
			(item.name && item.name.toLowerCase().includes(query)) ||
			(item.username && item.username.toLowerCase().includes(query))
		);
	});
}

// Render users table
function renderUsersTable() {
	console.log("renderUsersTable called with", filteredUsers.length, "users");
	$("#usersTable tbody").empty();

	if (filteredUsers.length === 0) {
		$("#usersTable tbody").html(
			`<tr>
	               <td colspan="6" class="text-center">لا يوجد مستخدمون</td>
	           </tr>`,
		);
	} else {
		$.each(filteredUsers, function (i, item) {
			var branchAccessText =
				item.branch_access_type === "all_branches"
					? "جميع الفروع"
					: "فروع محددة";
			var adminBadge =
				item.is_admin == 1
					? '<span class="badge badge-light-success">مدير</span>'
					: '<span class="badge badge-light-secondary">مستخدم</span>';

			$("#usersTable tbody").append(
				`<tr data-id="${item.id}">
	                   <td class="text-center" style="vertical-align: middle;">
					${escapeHtml(item.username)}</td>
	                   <td class="text-center" style="vertical-align: middle;">
					${escapeHtml(item.name)}</td>
	                   <td class="text-center" style="vertical-align: middle;">
					${branchAccessText}</td>
	                   <td class="text-center" style="vertical-align: middle;">
					${adminBadge}</td>
	                   <td class="text-center" style="vertical-align: middle;">
					${new Date(item.created_at).toLocaleDateString("en-SA")}</td>
	                   <td class="text-center">
	                       <div class="d-flex gap-1 justify-content-center">
	                           <button class="btn btn-sm btn-icon btn-light btn-active-light-primary editUserBtn"
	                                   type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="تعديل"
	                                   data-id="${item.id}">
	                               <i class="ki-duotone ki-notepad-edit text-primary fs-2">
	                                   <span class="path1"></span>
	                                   <span class="path2"></span>
	                               </i>
	                           </button>
	                           <button class="btn btn-sm btn-icon btn-light btn-active-light-warning resetPasswordBtn"
	                                   type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="إعادة تعيين كلمة المرور"
	                                   data-id="${item.id}">
	                               <i class="ki-duotone ki-key text-warning fs-2">
	                                   <span class="path1"></span>
	                                   <span class="path2"></span>
	                               </i>
	                           </button>
	                           <button class="btn btn-sm btn-icon btn-light btn-active-light-danger deleteUserBtn"
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

// Initialize the users table and event handlers
function initializeUsers() {
	filterUsers();
	renderUsersTable();
}

// Add user button click handler
$(document).on("click", ".addUserBtn", function () {
	$("#addUserModal").show();
});

// Cancel adding user
$("#cancelAddUser").click(function () {
	$("#addUserModal").hide();
});

// Handle branch access type change for add form
$(document).on("change", "#addBranchAccessType", function () {
	if ($(this).val() === "specific_branches") {
		$("#specificBranchesContainer").show();
	} else {
		$("#specificBranchesContainer").hide();
	}
});

// Handle form submission to add a new user
$("#addUserForm").submit(function (e) {
	e.preventDefault();
	var username = $("#addUsername").val().trim();
	var password = $("#addPassword").val().trim();
	var name = $("#addUserFullName").val().trim();
	var branchAccessType = $("#addBranchAccessType").val();
	var assignedBranches = [];
	var isAdmin = $("#addIsAdmin").is(":checked") ? 1 : 0;

	if (branchAccessType === "specific_branches") {
		$('input[name="assignedBranches[]"]:checked').each(function () {
			assignedBranches.push(parseInt($(this).val()));
		});
	}

	$.post(
		"user_data.php",
		{
			action: "add",
			username: username,
			password: password,
			name: name,
			branch_access_type: branchAccessType,
			assigned_branches: assignedBranches.length > 0 ? assignedBranches : null,
			is_admin: isAdmin,
		},
		function (response) {
			if (response.status === "success") {
				users.push(response.entry);
				$("#addUserModal").hide();
				$("#addUserForm")[0].reset();
				$("#specificBranchesContainer").hide();
				filterUsers();
				renderUsersTable();
				alert("تم إضافة المستخدم بنجاح");
			} else {
				alert(response.message || "Failed to add user.");
			}
		},
		"json",
	);
});

// Handle clicking the "Edit" button for users
$(document).on("click", ".editUserBtn", function () {
	var id = $(this).data("id");
	var user = users.find(function (item) {
		return item.id == id;
	});

	if (user) {
		$("#editUserId").val(user.id);
		$("#editUsername").val(user.username);
		$("#editUserFullName").val(user.name);
		$("#editBranchAccessType").val(user.branch_access_type);
		$("#editIsAdmin").prop("checked", user.is_admin == 1);

		// Handle branch assignments
		if (user.branch_access_type === "specific_branches") {
			$("#editSpecificBranchesContainer").show();
			// Clear all checkboxes first
			$(".edit-branch-checkbox").prop("checked", false);
			// Check assigned branches
			if (user.assigned_branches) {
				var assignedBranches = JSON.parse(user.assigned_branches);
				assignedBranches.forEach(function (branchId) {
					$("#edit_branch_" + branchId).prop("checked", true);
				});
			}
		} else {
			$("#editSpecificBranchesContainer").hide();
		}

		$("#editUserModal").show();
	}
});

// Handle branch access type change for edit form
$(document).on("change", "#editBranchAccessType", function () {
	if ($(this).val() === "specific_branches") {
		$("#editSpecificBranchesContainer").show();
	} else {
		$("#editSpecificBranchesContainer").hide();
	}
});

// Save edited user
$("#editUserForm").submit(function (e) {
	e.preventDefault();
	var id = $("#editUserId").val();
	var name = $("#editUserFullName").val().trim();
	var branchAccessType = $("#editBranchAccessType").val();
	var assignedBranches = [];
	var isAdmin = $("#editIsAdmin").is(":checked") ? 1 : 0;

	if (branchAccessType === "specific_branches") {
		$('input[name="editAssignedBranches[]"]:checked').each(function () {
			assignedBranches.push(parseInt($(this).val()));
		});
	}

	$.post(
		"user_data.php",
		{
			action: "edit",
			id: id,
			name: name,
			branch_access_type: branchAccessType,
			assigned_branches: assignedBranches.length > 0 ? assignedBranches : null,
			is_admin: isAdmin,
		},
		function (response) {
			if (response.status === "success") {
				// Update local data
				$.each(users, function (i, item) {
					if (item.id == id) {
						item.name = name;
						item.branch_access_type = branchAccessType;
						item.assigned_branches =
							assignedBranches.length > 0
								? JSON.stringify(assignedBranches)
								: null;
						item.is_admin = isAdmin;
					}
				});
				filterUsers();
				renderUsersTable();
				$("#editUserModal").hide();
				alert("تم تحديث المستخدم بنجاح");
			} else {
				// Show detailed error information on the page
				let debugInfo = "";
				if (response.debug) {
					debugInfo = JSON.stringify(response.debug, null, 2);
				}

				// Create a modal to display the error
				let modalHtml = `
				<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="errorModalLabel">خطأ في تحديث المستخدم</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="alert alert-danger">
									<strong>الرسالة:</strong> ${response.message || "Failed to update user."}
								</div>
								<div class="mb-3">
									<label class="form-label"><strong>معلومات التصحيح (يمكنك نسخها):</strong></label>
									<textarea class="form-control" rows="10" readonly id="debugInfo">${debugInfo}</textarea>
								</div>
								<button type="button" class="btn btn-secondary" onclick="copyDebugInfo()">نسخ المعلومات</button>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
							</div>
						</div>
					</div>
				</div>`;

				// Remove existing modal if it exists
				$("#errorModal").remove();

				// Add modal to body
				$("body").append(modalHtml);

				// Show modal
				$("#errorModal").modal("show");

				console.error("User update failed:", response);
			}
		},
		"json",
	);
});

// Cancel editing user
$("#cancelEditUser").click(function () {
	$("#editUserModal").hide();
});

// Handle reset password button
$(document).on("click", ".resetPasswordBtn", function () {
	var id = $(this).data("id");
	var newPassword = prompt("أدخل كلمة المرور الجديدة:");
	if (newPassword && newPassword.trim() !== "") {
		$.post(
			"user_data.php",
			{
				action: "reset_password",
				id: id,
				new_password: newPassword,
			},
			function (response) {
				if (response.status === "success") {
					alert("تم إعادة تعيين كلمة المرور بنجاح");
				} else {
					alert(response.message || "Failed to reset password.");
				}
			},
			"json",
		);
	}
});

// Handle delete user button
$(document).on("click", ".deleteUserBtn", function () {
	var id = $(this).data("id");
	if (confirm("هل أنت متأكد من حذف هذا المستخدم؟")) {
		$.post(
			"user_data.php",
			{
				action: "delete",
				id: id,
			},
			function (response) {
				if (response.status === "success") {
					users = users.filter(function (item) {
						return item.id != id;
					});
					filterUsers();
					renderUsersTable();
					alert("تم حذف المستخدم بنجاح");
				} else {
					alert(response.message || "Failed to delete user.");
				}
			},
			"json",
		);
	}
});

// Handle search input for users
$("#searchUserInput").on("input", function () {
	filterUsers();
	renderUsersTable();
});

// Function to copy debug info to clipboard
function copyDebugInfo() {
	var debugTextarea = document.getElementById("debugInfo");
	debugTextarea.select();
	debugTextarea.setSelectionRange(0, 99999); // For mobile devices

	try {
		document.execCommand("copy");
		alert("تم نسخ المعلومات إلى الحافظة");
	} catch (err) {
		// Fallback for browsers that don't support execCommand
		navigator.clipboard
			.writeText(debugTextarea.value)
			.then(function () {
				alert("تم نسخ المعلومات إلى الحافظة");
			})
			.catch(function (err) {
				alert("فشل في نسخ المعلومات. يرجى نسخها يدوياً.");
			});
	}
}
