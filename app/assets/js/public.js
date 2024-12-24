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

  message(type, message) {
    if (type == "success") {
      iziToast.success({
        title: "AstroFx",
        position: "topRight",
        progressBarColor: "rgb(0, 255, 184)",
        message: message,
      });
    } else if (type == "info") {
      iziToast.info({
        title: "AstroFx",
        position: "topRight",
        progressBarColor: "rgb(0, 255, 184)",
        message: message,
      });
    } else {
      iziToast.error({
        title: "AstroFx",
        position: "topRight",
        progressBarColor: "rgb(0, 255, 184)",
        message: message,
      });
    }
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

  async updateDashboard() {
    main.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/getMyDetails", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === 0) {
        $.unblockUI();
        setItem("wallet", response.data.wallet);
        setItem("wallet_network", response.data.wallet_network);
        $("#fullname").html(getItem("fullname"));
        $("#email").html(getItem("email"));
        $("#accountID").html(getItem("customerid"));
        $("#firstname").html(getItem("fullname"));
        $("#balance").html(response.data.details.balance);
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async withdrawal(formid) {
    if ($("#network").val() == "" || $("#amount").val() == "" || $("#wallet").val() == "") {
      $(".errorModal").html("Please complete the form");
      $("#errorModal").modal("show");
      return;
    }
    main.blockUI("Processing..");
    $("#userid").val(getItem("userid"));
    const formData = new FormData(document.getElementById(`${formid}`));
    // return;
    try {
      const response = await axios.post("api/index.php/withdrawal", formData, {
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
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async deposit() {
    main.blockUI("Processing..");
    const requestData = new FormData();
    requestData.append("userid", getItem("userid"));
    requestData.append("amount", $("#d_amount").val());
    try {
      const response = await axios.post("api/index.php/deposit", requestData, {
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
        $("#successModal").modal("show");
        $(".successModal").html(response.data.message);
      } else {
        throw new Error("Error");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async invest(formid) {
    if ($("#amount").val() == "") {
      $(".errorModal").html("Please enter a valid amount.");
      $("#errorModal").modal("show");
      return;
    }
    main.blockUI("Processing..");
    $("#userid").val(getItem("userid"));
    $("#package_id").val(getItem("package_id"));
    const formData = new FormData(document.getElementById(`${formid}`));
    try {
      const response = await axios.post("api/index.php/invest", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $.unblockUI();
        $("#investModal").modal("hide");
        $(".errorModal").html(response.data.message);
        $("#errorModal").modal("show");
      } else if (response.data.statuscode === 0) {
        $("#investModal").modal("hide");
        $.unblockUI();
        $(".successModal").html(`${getItem("planeName")} investment was successful`);
        $("#successModal").modal("show");
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  countdown(duration) {
    $("#countdown").html("");
    var display = document.getElementById("countdown");
    var timer = duration,
      minutes,
      seconds;
    var interval = setInterval(function () {
      minutes = parseInt(timer / 60, 10);
      seconds = parseInt(timer % 60, 10);

      minutes = minutes < 10 ? "0" + minutes : minutes;
      seconds = seconds < 10 ? "0" + seconds : seconds;

      display.textContent = minutes + ":" + seconds;

      if (--timer < 0) {
        clearInterval(interval);
        display.textContent = "00:00";
        // You can add code here to perform an action when the timer reaches zero.
      }
    }, 1000);
  }

  clickCopy() {
    var address = document.getElementById("waddress").innerText;
    navigator.clipboard
      .writeText(address)
      .then(function () {
        var message = document.getElementById("copMess");
        message.textContent = "Copied!";
        setTimeout(function () {
          message.textContent = "Click to copy";
        }, 2000); // Clear the message after 2 seconds
      })
      .catch(function (err) {
        console.error("Failed to copy text: ", err);
      });
  }

  generateAddress() {
    if ($("#d_amount").val() == "") {
      $(".errorModal").html("Please enter a valid amount");
      $("#errorModal").modal("show");
      return;
    }
    var fiveMinutes = 60 * 10;
    main.countdown(fiveMinutes);
    $("#qrcode").html("");
    $("#d_amount2").html($("#d_amount").val());
    $("#waddress").html(getItem("wallet"));
    $("#wallet_network").html(getItem("wallet_network"));
    var walletAddress = getItem("wallet");
    var qrcode = new QRCode(document.getElementById("qrcode"), {
      text: walletAddress,
      width: 128,
      height: 128,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H,
    });
    $("#fundWallet1").modal("hide");
    $("#usdtBarcode").modal("show");
  }

  async transactions() {
    main.blockUI("Please wait..");
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

  async investmentPlans() {
    main.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/getpackages", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      var body = "";
      if (response.data.statuscode === 0) {
        $.unblockUI();
        response.data.plans.forEach(function (data) {
          body += `<div class="col-xl-4 mb-5 mb-xl-10">
                    <div id="kt_sliders_widget_2_slider"
                      class="card card-flush carousel carousel-custom carousel-stretch slide h-xl-100 pointer-event"
                      data-bs-ride="carousel" data-bs-interval="5500">
                      <div class="card-header pt-5">

                        <h4 class="card-title d-flex align-items-start flex-column">
                          <span class="card-label fw-bold text-gray-800">${data.name}</span>
                          <span class="text-gray-400 mt-1 fw-bold fs-7">Duration <span>${data.duration}</span>days</span>
                        </h4>
                        <div class="card-toolbar">
                          <ol
                            class="p-0 m-0 carousel-indicators carousel-indicators-bullet carousel-indicators-active-success">
                            <li data-bs-target="#kt_sliders_widget_2_slider"
                              data-bs-slide-to="0" class="ms-1 active"
                              aria-current="true"></li>
                          </ol>
                        </div>
                      </div>
                      <div class="card-body py-6">
                        <div class="carousel-inner">
                          <div class="carousel-item show active">
                            <div class="d-flex align-items-center mb-9">
                              <div class="symbol symbol-70px symbol-circle me-5">
                                <span class="symbol-label bg-light-success">
                                  <span
                                    class="svg-icon svg-icon-3x svg-icon-success">
                                    <img src="assets/media/icon.png"
                                      height="30">
                                  </span>
                                </span>
                              </div>
                              <div class="m-0">
                                <h4 class="fw-bold text-gray-800 mb-3">$<span>${data.minimum}</span> - $<span>${data.maximum}</span>
                                </h4>
                                <div class="d-flex d-grid gap-5">
                                  <div
                                    class="d-flex flex-column flex-shrink-0 me-4">
                                    <span
                                      class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-2">
                                      <span
                                        class="svg-icon svg-icon-6 svg-icon-gray-600 me-2">
                                        <svg width="24" height="24"
                                          viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <rect opacity="0.3" x="2" y="2"
                                            width="20" height="20"
                                            rx="5" fill="currentColor">
                                          </rect>
                                          <path
                                            d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5789 10.4211 16.6334 10.8156 16.2342L14.3054 12.7029C14.6903 12.3134 14.6903 11.6866 14.3054 11.2971L10.8156 7.76582C10.4211 7.3666 9.7616 7.42107 9.43792 7.87962C9.18488 8.23809 9.22669 8.72669 9.53696 9.03696L11.9343 11.4343C12.2467 11.7467 12.2467 12.2533 11.9343 12.5657Z"
                                            fill="currentColor"></path>
                                        </svg>
                                      </span>
                                      Profit</span>
                                    <span
                                      class="d-flex align-items-center text-gray-400 fw-bold fs-7">
                                      <span
                                        class="svg-icon svg-icon-6 svg-icon-gray-600 me-2">
                                        <svg width="24" height="24"
                                          viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <rect opacity="0.3" x="2" y="2"
                                            width="20" height="20"
                                            rx="5" fill="currentColor">
                                          </rect>
                                          <path
                                            d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5789 10.4211 16.6334 10.8156 16.2342L14.3054 12.7029C14.6903 12.3134 14.6903 11.6866 14.3054 11.2971L10.8156 7.76582C10.4211 7.3666 9.7616 7.42107 9.43792 7.87962C9.18488 8.23809 9.22669 8.72669 9.53696 9.03696L11.9343 11.4343C12.2467 11.7467 12.2467 12.2533 11.9343 12.5657Z"
                                            fill="currentColor"></path>
                                        </svg>
                                      </span>Capital

                                    </span>
                                  </div>
                                </div>

                              </div>
                              <div class="m-0">
                                <div
                                  class="bg-light-danger rounded-2 px-3 py-2">
                                  <div class="m-0">
                                    <span
                                      class="text-danger fw-bolder d-block fs-3 lh-1 ls-n1 mb-1"><span>${data.percentage}</span>%
                                    </span>
                                    <span
                                      class="text-gray-500 fw-semibold fs-6">Returns</span>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="m-0">
                              <a href="#" class="btn btn-sm btn-success mb-2"
                                data-bs-toggle="modal"
                                data-bs-target="#kt_modal_create_campaign" onclick="main.investModal('${data.package_id}', '${data.name}', '${data.percentage}', '${data.minimum}', '${data.maximum}', '${data.duration}')">Invest
                                Now</a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>`;
        });
        $("#plans").html(body);
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async activePlans() {
    main.blockUI("Please wait..");
    try {
      const response = await axios.get("api/index.php/getMyServices", {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      var body = "";
      if (response.data.statuscode === 0) {
        $.unblockUI();
        if (response.data.services.reverse().length > 0) {
          response.data.services.forEach(function (data) {
            var status2 = data.due_amount == 0 ? "Running" : "Claim reward";
            if (data.due_amount == 0) {
              body += `<div class="col-xl-4 mb-5 mb-xl-10">
                      <div id="kt_sliders_widget_2_slider"
                        class="card card-flush carousel carousel-custom carousel-stretch slide h-xl-100 pointer-event"
                        data-bs-ride="carousel" data-bs-interval="5500">
                        <div class="card-header pt-5">

                          <h4 class="card-title d-flex align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">${data.name}</span>
                          </h4>
                          <div class="card-toolbar">
                            <ol
                              class="p-0 m-0 carousel-indicators carousel-indicators-bullet carousel-indicators-active-success">
                              <li data-bs-target="#kt_sliders_widget_2_slider"
                                data-bs-slide-to="0" class="ms-1 active"
                                aria-current="true"></li>
                            </ol>
                          </div>
                        </div>
                        <div class="card-body py-6">
                          <div class="carousel-inner">
                            <div class="carousel-item show active">
                              <div class="d-flex align-items-center mb-9">
                                <div class="symbol symbol-70px symbol-circle me-5">
                                  <span class="symbol-label bg-light-success">
                                    <span
                                      class="">
                                      <img src="assets/media/clock2.gif"
                                        height="50">
                                    </span>
                                  </span>
                                </div>
                                <div class="m-0">
                                  <div class="fs-3 fw-bold text-success mb-3">${status2}</span></div>
                                  <div class="d-flex d-grid gap-5">
                                    <div
                                      class="d-flex flex-column flex-shrink-0 me-4">
                                      <span
                                        class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-2">
                                        <span
                                          class="svg-icon svg-icon-6 svg-icon-gray-600 me-2">
                                          <svg width="24" height="24"
                                            viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.3" x="2" y="2"
                                              width="20" height="20"
                                              rx="5" fill="currentColor">
                                            </rect>
                                            <path
                                              d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5789 10.4211 16.6334 10.8156 16.2342L14.3054 12.7029C14.6903 12.3134 14.6903 11.6866 14.3054 11.2971L10.8156 7.76582C10.4211 7.3666 9.7616 7.42107 9.43792 7.87962C9.18488 8.23809 9.22669 8.72669 9.53696 9.03696L11.9343 11.4343C12.2467 11.7467 12.2467 12.2533 11.9343 12.5657Z"
                                              fill="currentColor"></path>
                                          </svg>
                                        </span>Due date:
                                      </span>
                                      <span
                                        class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-2">
                                        <span
                                          class="svg-icon svg-icon-6 svg-icon-gray-600 me-2">
                                          <svg width="24" height="24"
                                            viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.3" x="2" y="2"
                                              width="20" height="20"
                                              rx="5" fill="currentColor">
                                            </rect>
                                            <path
                                              d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5789 10.4211 16.6334 10.8156 16.2342L14.3054 12.7029C14.6903 12.3134 14.6903 11.6866 14.3054 11.2971L10.8156 7.76582C10.4211 7.3666 9.7616 7.42107 9.43792 7.87962C9.18488 8.23809 9.22669 8.72669 9.53696 9.03696L11.9343 11.4343C12.2467 11.7467 12.2467 12.2533 11.9343 12.5657Z"
                                              fill="currentColor"></path>
                                          </svg>
                                        </span>${data.due_date}
                                      </span>
                                    </div>
                                  </div>

                                </div>
                              </div>
                              <div class="m-0 row col-lg-12">
                                <div class="col-7">
                                  <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-2">
                                    <div class="fs-6 text-gray-700 ms-5">$${data.due_amount} ready to claim</div>
                                  </div>
                                </div>
                                <div class="col-5"><button disabled="" class="btn btn-sm btn-light mb-2"><i class="fa fa-ban"></i>Claim</button></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>`;
            } else {
              body += `<div class="col-xl-4 mb-5 mb-xl-10">
                      <div id="kt_sliders_widget_2_slider"
                        class="card card-flush carousel carousel-custom carousel-stretch slide h-xl-100 pointer-event"
                        data-bs-ride="carousel" data-bs-interval="5500">
                        <div class="card-header pt-5">

                          <h4 class="card-title d-flex align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">${data.name}</span>
                          </h4>
                          <div class="card-toolbar">
                            <ol
                              class="p-0 m-0 carousel-indicators carousel-indicators-bullet carousel-indicators-active-success">
                              <li data-bs-target="#kt_sliders_widget_2_slider"
                                data-bs-slide-to="0" class="ms-1 active"
                                aria-current="true"></li>
                            </ol>
                          </div>
                        </div>
                        <div class="card-body py-6">
                          <div class="carousel-inner">
                            <div class="carousel-item show active">
                              <div class="d-flex align-items-center mb-9">
                                <div class="symbol symbol-70px symbol-circle me-5">
                                  <span class="symbol-label bg-light-success">
                                    <span
                                      class="">
                                      <img src="assets/media/clock2.png"
                                        height="50">
                                    </span>
                                  </span>
                                </div>
                                <div class="m-0">
                                  <div class="fs-3 fw-bold text-danger mb-3">${status2}</span></div>
                                  <div class="d-flex d-grid gap-5">
                                    <div
                                      class="d-flex flex-column flex-shrink-0 me-4">
                                      <span
                                        class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-2">
                                        <span
                                          class="svg-icon svg-icon-6 svg-icon-gray-600 me-2">
                                          <svg width="24" height="24"
                                            viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.3" x="2" y="2"
                                              width="20" height="20"
                                              rx="5" fill="currentColor">
                                            </rect>
                                            <path
                                              d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5789 10.4211 16.6334 10.8156 16.2342L14.3054 12.7029C14.6903 12.3134 14.6903 11.6866 14.3054 11.2971L10.8156 7.76582C10.4211 7.3666 9.7616 7.42107 9.43792 7.87962C9.18488 8.23809 9.22669 8.72669 9.53696 9.03696L11.9343 11.4343C12.2467 11.7467 12.2467 12.2533 11.9343 12.5657Z"
                                              fill="currentColor"></path>
                                          </svg>
                                        </span>Due date:
                                      </span>
                                      <span
                                        class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-2">
                                        <span
                                          class="svg-icon svg-icon-6 svg-icon-gray-600 me-2">
                                          <svg width="24" height="24"
                                            viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.3" x="2" y="2"
                                              width="20" height="20"
                                              rx="5" fill="currentColor">
                                            </rect>
                                            <path
                                              d="M11.9343 12.5657L9.53696 14.963C9.22669 15.2733 9.18488 15.7619 9.43792 16.1204C9.7616 16.5789 10.4211 16.6334 10.8156 16.2342L14.3054 12.7029C14.6903 12.3134 14.6903 11.6866 14.3054 11.2971L10.8156 7.76582C10.4211 7.3666 9.7616 7.42107 9.43792 7.87962C9.18488 8.23809 9.22669 8.72669 9.53696 9.03696L11.9343 11.4343C12.2467 11.7467 12.2467 12.2533 11.9343 12.5657Z"
                                              fill="currentColor"></path>
                                          </svg>
                                        </span>${data.due_date}
                                      </span>
                                    </div>
                                  </div>

                                </div>
                              </div>
                              <div class="m-0 row col-lg-12">
                                <div class="col-7">
                                  <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-2">
                                    <div class="fs-6 text-gray-700 ms-5">$${data.due_amount} ready to claim</div>
                                  </div>
                                </div>
                                <div class="col-5"><button onclick="main.claimReward('${data.userid}', '${data.id}', '${data.due_amount}')" class="btn btn-sm btn-success mb-2"><i class="fa fa-unlock"></i>Claim</button></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>`;
            }
          });
        } else {
          body = `<div class="col-xl-12 mb-5 mb-xl-10">
                    <div id="kt_sliders_widget_2_slider" class="card card-flush carousel carousel-custom carousel-stretch slide h-xl-100 pointer-event" data-bs-ride="carousel" data-bs-interval="5500">
                      <div class="card-body py-6">
                        <div class="carousel-inner">
                          <div class="carousel-item show active d-flex flex-center">
                            <img src="assets/media/empty.gif" height="180">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>`;
        }
        $("#plans").html(body);
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }

  investModal(id, name, perc, min, max, dur) {
    setItem("package_id", id);
    setItem("planeName", name);
    $(".planeName").html(name);
    $("#perc").html(perc);
    $("#dura").html(dur);
    $("#investModal").modal("show");
  }

  async claimReward(uid, sid, amount) {
    main.blockUI("Processing..");
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

let main = new Main();
