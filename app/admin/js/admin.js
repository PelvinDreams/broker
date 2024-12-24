class Main {
  validateFormData(formData) {
    var isValid = true;
    for (var key in formData) {
      if (formData.hasOwnProperty(key)) {
        var value = formData[key];
        var field = $('[fields="' + key + '"]');
        if (field.attr("required") && value.trim() === "") {
          field.next(".error-message").remove();
          field.removeClass("error");
          field.after('<div class="error-message invalid-feedback1">This field is required</div>');
          field.addClass("error");
          isValid = false;
        } else {
          field.next(".error-message").remove();
          field.removeClass("error");
        }
      }
    }
    return isValid;
  }

  blockUI(displayMessage) {
    $.blockUI({
      message: `<div class="blockui-message d-flex align-items-center fw-bolder bg-white w-auto m-0 text-main" style="border-radius: 0.475rem; box-shadow: 0 0 50px 0 rgb(82 63 105 / 15%); color: #7e8299; padding: 0.85rem 1.75rem!important;"><span class="spinner-border text-main me-2"></span class="text-main">
              ${displayMessage}...</div>`,
      baseZ: 2000,
      css: {
        display: "flex",
        padding: "20%",
        margin: "0",
        width: "100%",
        height: "100dvh",
        top: "0%",
        left: "0%",
        textAlign: "center",
        color: "#000",
        border: "0px solid #aaa",
        backgroundColor: "rgba(255,255,255,0.6)",
        cursor: "wait",
        alignItems: "center",
        justifyContent: "center",
        position: "absolute",
        boxSizing: "border-box",
      },
    });
  }

  async statistics() {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/statistics", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        $("#fullname").html(getItem("fullname"));
        $("#email").html(getItem("email"));
        $("#firstname").html(getItem("fullname"));
        $(".active_users").html(response.data.active_users);
        $(".s_users").html(response.data.suspended_users);
        $(".dwallet").html(response.data.wallet);
        $(".dnetwork").html(response.data.wallet_network);
        $("#walletd").val(response.data.wallet);
        $("#networkd").val(response.data.wallet_network);
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async transactions() {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/transactions", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      var body;
      if (response.data.statuscode === 0) {
        $.unblockUI();
        var sno = 0;
        response.data.history.reverse().forEach(function (data) {
          sno++;
          var s_status;
          var type = data.type == "Deposit" || data.type == "Returns" ? `<div class="badge badge-light-primary">${data.type}</div>` : `<div class="badge badge-light-danger">${data.type}</div>`;
          if (data.status == "Successful") {
            s_status = '<div class="badge badge-light-primary">Successful</div>';
          } else if (data.status == "Pending") {
            s_status = '<div class="badge badge-light-warning">Pending</div>';
          } else {
            s_status = '<div class="badge badge-light-danger">Failed</div>';
          }
          body += `<tr>
                        <td class="text-start pe-0">
                            <span class="text-gray-600 fw-bold fs-6">${sno}</span>
                        </td>
                        <td class="text-start pe-0">
                            <span class="text-gray fw-bold fs-6">${type}</span>
                        </td>
                        <td class="text-start pe-0">
                          <span class="text-gray-600 fw-bold fs-6">$${data.amount}</span>
                        </td>
                        <td class="text-start pe-0">
                        ${s_status}
                        </td>
                        <td class="text-start pe-0">
                            <span class="text-gray-600 fw-bold fs-6">${data.date}</span>
                        </td>
                    </tr>`;
        });
        $("#tbodyp").html(body);
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async getAllUsers() {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/getallusers", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        const jsonString = JSON.stringify(response.data.users.reverse());
        var json = JSON.parse(jsonString);
        $("#packages").DataTable().destroy();
        var table = $("#packages").DataTable({
          searching: true,
          responsive: true,
          data: json,
          dom: "Btp",
          columns: [{ title: "S/N", data: "" }, { title: "Full Name", data: "firstname" }, { title: "Username", data: "username" }, { title: "Status", data: "status" }, { title: "Email", data: "email" }, { title: "Balance", data: "balance" }, { title: "Country", data: "country" }, { title: "Actions" }],
          columnDefs: [
            {
              defaultContent: "-",
              targets: "_all",
            },
            {
              targets: 0,
              orderable: true,
              searchable: true,
              render: function (data, type, row, meta) {
                var sno = 0;
                sno = meta.row + 1;
                var content = sno;
                return content;
              },
            },
            {
              targets: 1,
              orderable: true,
              searchable: true,
              render: function (data, type, row, meta) {
                var name = row.firstname + " " + row.lastname;
                return name;
              },
            },
            {
              targets: 3,
              orderable: true,
              searchable: true,
              render: function (data, type, row) {
                var retstr = "";
                if (row.status == "Active") {
                  retstr = `<span class="badge badge-light-success">${row.status}</span>`;
                } else {
                  retstr = `<span class="badge badge-light-danger">${row.status}</span>`;
                }

                return retstr;
              },
            },
            {
              targets: -1,
              orderable: false,
              searchable: false,
              render: function (data, type, row) {
                var retstr = "";
                if (row.status == "Active") {
                  retstr = `<button onclick="admin.viewUser('${row.id}', '${row.firstname}', '${row.lastname}', '${row.phone}', '${row.country}', '${row.email}')" class="btn btn-sm btn-light-primary mb-2">View</button> <button onclick="admin.readyDanger('suspendUser', '${row.id}')" class="btn btn-sm btn-light-warning mb-2">Suspend</button> <button onclick="admin.readyDanger('deleteUser', '${row.id}')" class="btn btn-sm btn-light-danger mb-2">Delete</button>`;
                } else {
                  retstr = `<button onclick="admin.viewUser('${row.id}', '${row.firstname}', '${row.lastname}', '${row.phone}', '${row.country}', '${row.email}')" class="btn btn-sm btn-light-primary mb-2">View</button><button onclick="admin.readyDanger('unSuspendUser', '${row.id}')" class="btn btn-sm btn-light-primary mb-2">Unsuspend</button> <button onclick="admin.readyDanger('deleteUser', '${row.id}')" class="btn btn-sm btn-light-danger mb-2">Delete</button>`;
                }

                return retstr;
              },
            },
          ],
        });
        $("#search").keyup(function () {
          table.search($(this).val()).draw();
        });
        $("#packages thead").addClass("bg-secondary");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async allTransactions(limit = null) {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/allTransactions", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        const jsonString = JSON.stringify(response.data.transactions.reverse());
        var json = JSON.parse(jsonString);
        $("#packages").DataTable().destroy();
        var table = $("#packages").DataTable({
          searching: true,
          responsive: true,
          data: json,
          dom: "Btp",
          columns: [
            { title: "S/N", data: "" },
            { title: "Full Name", data: "name" },
            { title: "Type", data: "type" },
            { title: "Amount", data: "amount" },
            { title: "Status", data: "status" },
            { title: "Date", data: "date" },
          ],
          columnDefs: [
            {
              defaultContent: "-",
              targets: "_all",
            },
            {
              targets: 0,
              orderable: true,
              searchable: true,
              render: function (data, type, row, meta) {
                var sno = 0;
                sno = meta.row + 1;
                var content = sno;
                return content;
              },
            },
            {
              targets: 4,
              orderable: false,
              searchable: false,
              render: function (data, type, row) {
                var retstr = "";
                if (row.status == "Successful") {
                  retstr = `<span class="badge badge-light-success">${row.status}</span>`;
                } else if (row.status == "Pending") {
                  retstr = `<span class="badge badge-light-warning">${row.status}</span>`;
                } else {
                  retstr = `<span class="badge badge-light-danger">${row.status}</span>`;
                }

                return retstr;
              },
            },
          ],
        });
        $("#search").keyup(function () {
          table.search($(this).val()).draw();
        });
        $("#packages thead").addClass("bg-secondary");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async depositRequest() {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/depositRequest", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        const jsonString = JSON.stringify(response.data.transactions.reverse());
        var json = JSON.parse(jsonString);
        $("#packages").DataTable().destroy();
        var table = $("#packages").DataTable({
          searching: true,
          responsive: true,
          data: json,
          dom: "Btp",
          columns: [{ title: "S/N", data: "" }, { title: "Full Name", data: "name" }, { title: "Amount", data: "amount" }, { title: "Date", data: "date" }, { title: "Actions" }],
          columnDefs: [
            {
              defaultContent: "-",
              targets: "_all",
            },
            {
              targets: 0,
              orderable: true,
              searchable: true,
              render: function (data, type, row, meta) {
                var sno = 0;
                sno = meta.row + 1;
                var content = sno;
                return content;
              },
            },
            {
              targets: -1,
              orderable: false,
              searchable: false,
              render: function (data, type, row) {
                var retstr = `<button onclick="admin.readyDanger('approveDeposit', '${row.id}')" class="btn btn-sm btn-light-primary mb-2">Approve</button> <button onclick="admin.readyDanger('declineDeposit', '${row.id}')" class="btn btn-sm btn-light-danger mb-2">Decline</button>`;
                return retstr;
              },
            },
          ],
        });
        $("#search").keyup(function () {
          table.search($(this).val()).draw();
        });
        $("#packages thead").addClass("bg-secondary");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async withdrawalRequest() {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/withdrawalRequest", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        const jsonString = JSON.stringify(response.data.transactions.reverse());
        var json = JSON.parse(jsonString);
        $("#packages").DataTable().destroy();
        var table = $("#packages").DataTable({
          searching: true,
          responsive: true,
          data: json,
          dom: "Btp",
          columns: [{ title: "S/N", data: "" }, { title: "Full Name", data: "name" }, { title: "Amount", data: "amount" }, { title: "Wallet", data: "wallet" }, { title: "NetWork", data: "network" }, { title: "Date", data: "date" }, { title: "Actions" }],
          columnDefs: [
            {
              defaultContent: "-",
              targets: "_all",
            },
            {
              targets: 0,
              orderable: true,
              searchable: true,
              render: function (data, type, row, meta) {
                var sno = 0;
                sno = meta.row + 1;
                var content = sno;
                return content;
              },
            },
            {
              targets: -1,
              orderable: false,
              searchable: false,
              render: function (data, type, row) {
                var retstr = `<button onclick="admin.readyDanger('approveWithdrawal', '${row.id}')" class="btn btn-sm btn-light-primary mb-2">Approve</button> <button onclick="admin.readyDanger('declineWithdrawal', '${row.id}')" class="btn btn-sm btn-light-danger mb-2">Decline</button>`;
                return retstr;
              },
            },
          ],
        });
        $("#search").keyup(function () {
          table.search($(this).val()).draw();
        });
        $("#packages thead").addClass("bg-secondary");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async getAllPackages() {
    admin.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/getpackages", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        const jsonString = JSON.stringify(response.data.plans.reverse());
        var json = JSON.parse(jsonString);
        $("#packages").DataTable().destroy();
        var table = $("#packages").DataTable({
          searching: true,
          responsive: true,
          data: json,
          dom: "Btp",
          columns: [{ title: "S/N", data: "" }, { title: "Package Name", data: "name" }, { title: "Percentage(%)", data: "percentage" }, { title: "Duration", data: "duration" }, { title: "Maximum Amount", data: "maximum" }, { title: "Minimum Amount", data: "minimum" }, { title: "Actions" }],
          columnDefs: [
            {
              defaultContent: "-",
              targets: "_all",
            },
            {
              targets: 0,
              orderable: true,
              searchable: true,
              render: function (data, type, row, meta) {
                var sno = 0;
                sno = meta.row + 1;
                var content = sno;
                return content;
              },
            },
            {
              targets: -1,
              orderable: false,
              searchable: false,
              render: function (data, type, row) {
                var retstr = `<button onclick="admin.viewPackage('${row.package_id}', '${row.name}', '${row.duration}', '${row.maximum}', '${row.minimum}', '${row.percentage}')" class="btn btn-sm btn-light-primary mb-2">Edit</button> <button onclick="admin. readyDanger('deletePackage', '${row.package_id}')" class="btn btn-sm btn-light-danger mb-2">Delete</button>`;
                return retstr;
              },
            },
          ],
        });
        $("#search").keyup(function () {
          table.search($(this).val()).draw();
        });
        $("#packages thead").addClass("bg-secondary");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  viewPackage(pid = null, name = null, duration = null, maximum = null, minimum = null, ppercentage = null) {
    if (pid != null && name != null) {
      setItem("buttonType", "updatePackage");
      $(".packageT").html("Edit Package");
      $("#pname").val(name);
      $("#pduration").val(duration);
      $("#pmaximum").val(maximum);
      $("#pminimum").val(minimum);
      $("#pid").val(pid);
      $("#ppercentage").val(ppercentage);
      $("#editPackageMOdal").modal("show");
    } else {
      setItem("buttonType", "appPackage");
      $(".packageT").html("Add Package");
      $("#pname").val("");
      $("#pduration").val("");
      $("#pmaximum").val("");
      $("#pminimum").val("");
      $("#pid").val("");
      $("#ppercentage").val("");
      $("#editPackageMOdal").modal("show");
    }
  }

  viewUser(uid, ufname, ulname, uphone, ucountry, uemail) {
    $("#ufname").val(ufname);
    $("#ulname").val(ulname);
    $("#uphone").val(uphone);
    $("#ucountry").val(ucountry);
    $("#uemail").val(uemail);
    $("#u_id").val(uid);
    $("#editUserModal").modal("show");
  }

  readyDanger(type = null, id) {
    if (type == "deletePackage") {
      setItem("dpid", id);
      $(".infoModal").html("Are you sure you want to delete this package?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">Cancel</button>
                              <button type="button" onclick="admin.deletePackage()" class="btn btn-sm btn-danger">
                                <span class="indicator-label">Delete</span>
                              </button>`);
    } else if (type == "deleteUser") {
      setItem("uid", id);
      $(".infoModal").html("Are you sure you want to delete this user?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">Cancel</button>
                              <button type="button" onclick="admin.deleteUser()" class="btn btn-sm btn-danger">
                                <span class="indicator-label">Delete</span>
                              </button>`);
    } else if (type == "suspendUser") {
      setItem("uid", id);
      $(".infoModal").html("Are you sure you want to suspend this user?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">NO</button>
                              <button type="button" onclick="admin.suspendUser()" class="btn btn-sm btn-danger">
                                <span class="indicator-label">YES</span>
                              </button>`);
    } else if (type == "unSuspendUser") {
      setItem("uid", id);
      $(".infoModal").html("Are you sure you want to make this user Active?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">NO</button>
                              <button type="button" onclick="admin.unSuspendUser()" class="btn btn-sm btn-success">
                                <span class="indicator-label">YES</span>
                              </button>`);
    } else if (type == "approveWithdrawal") {
      setItem("tid", id);
      $(".infoModal").html("Are you sure you want to approve this transactions?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">Cancel</button>
                              <button type="button" onclick="admin.approveWithdrawal()" class="btn btn-sm btn-success">
                                <span class="indicator-label">Approve</span>
                              </button>`);
    } else if (type == "declineWithdrawal") {
      setItem("tid", id);
      $(".infoModal").html("Are you sure you want to decline this transactions?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">Cancel</button>
                              <button type="button" onclick="admin.declineWithdrawal()" class="btn btn-sm btn-danger">
                                <span class="indicator-label">Decline</span>
                              </button>`);
    } else if (type == "approveDeposit") {
      setItem("tid", id);
      $(".infoModal").html("Are you sure you want to approve this transactions?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">Cancel</button>
                              <button type="button" onclick="admin.approveDeposit()" class="btn btn-sm btn-success">
                                <span class="indicator-label">Approve</span>
                              </button>`);
    } else if (type == "declineDeposit") {
      setItem("tid", id);
      $(".infoModal").html("Are you sure you want to decline this transactions?");
      $("#infoModal").modal("show");
      $("#infoButton").html(`<button type="button" data-bs-dismiss="modal"
                                class="btn btn-sm btn-light me-3">Cancel</button>
                              <button type="button" onclick="admin.declineDeposit()" class="btn btn-sm btn-danger">
                                <span class="indicator-label">Decline</span>
                              </button>`);
    } else {
      $(".errorModal").html("Unknown type");
      $("#errorModal").modal("show");
    }
  }

  async updateUserDetails() {
    admin.blockUI("Processing..");
    const requestData = new FormData(document.getElementById("editUserDetails"));
    try {
      const response = await axios.post("api/index.php/updateUserDetails", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#editUserModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        $("#editUserModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async updateWallet() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("walletd", $("#walletd").val());
    requestData.append("networkd", $("#networkd").val());
    try {
      const response = await axios.post("api/index.php/updateWallet", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#update_wallet").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.statistics();
        $.unblockUI();
        $("#update_wallet").modal("hide");
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async updatePackage() {
    admin.blockUI("Processing..");
    const requestData = new FormData(document.getElementById("updateP"));
    try {
      var response = "";
      if (getItem("buttonType") == "updatePackage") {
        response = await axios.post("api/index.php/updatePackage", requestData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
      } else {
        response = await axios.post("api/index.php/addPackage", requestData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
      }
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#editPackageMOdal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.getAllPackages();
        $.unblockUI();
        $("#editPackageMOdal").modal("hide");
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async deletePackage() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("dpid", getItem("dpid"));
    try {
      const response = await axios.post("api/index.php/deletePackage", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.getAllPackages();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async suspendUser() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("userid", getItem("uid"));
    try {
      const response = await axios.post("api/index.php/suspendUser", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.getAllUsers();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async unSuspendUser() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("userid", getItem("uid"));
    try {
      const response = await axios.post("api/index.php/unSuspendUser", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.getAllUsers();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async approveDeposit() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("tid", getItem("tid"));
    try {
      const response = await axios.post("api/index.php/approveTrans", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.depositRequest();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async declineDeposit() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("tid", getItem("tid"));
    try {
      const response = await axios.post("api/index.php/declineTrans", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.depositRequest();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async approveWithdrawal() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("tid", getItem("tid"));
    try {
      const response = await axios.post("api/index.php/approveTrans", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.withdrawalRequest();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async declineWithdrawal() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("tid", getItem("tid"));
    try {
      const response = await axios.post("api/index.php/declineTrans", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.withdrawalRequest();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async deleteUser() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("userid", getItem("uid"));
    try {
      const response = await axios.post("api/index.php/deleteUser", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#infoModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        admin.getAllUsers();
        $("#infoModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async fundAccount() {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("fusername", $("#fusername").val());
    requestData.append("famount", $("#famount").val());
    try {
      const response = await axios.post("api/index.php/fundAccount", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        $.unblockUI();
        $(".successModal").html(response.data.message);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async editPackage(pid) {
    admin.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("userid", uid);
    requestData.append("pid", sid);
    try {
      const response = await axios.post("api/index.php/claimReward", requestData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        $.unblockUI();
        $(".successModal").html(`$${amount} has just been added to your balance`);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }
}
let admin = new Main();
