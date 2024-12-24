class Auth {
  async signUpUser() {
    $(".alert-danger").hide();
    if ($("#firstname").val() == "" || $("#lastname").val() == "" || $("#username").val() == "" || $("#email").val() == "" || $("#country").val() == "" || $("#phone").val() == "" || $("#password").val() == "" || $("#confirm_password").val() == "") {
      $(".alert-danger").show();
      $(".text-danger").html("Please complete the form.");
      return;
    }
    if ($("#password").val() !== $("#confirm_password").val()) {
      $(".alert-danger").show();
      $(".text-danger").html("Your password do not match");
    }
    $(".formSpin").css("display", "inline-block").show();
    const formData = new FormData(document.getElementById("signupform"));
    try {
      const response = await axios.post("../api/index.php/auth", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $(".alert-danger").show();
        $(".text-danger").html(response.data.message);
        $(".formSpin").hide();
        return;
      }
      if (response.data.statuscode === 0) {
        window.location.href = "sign-in.html?status=successful";
      } else {
        throw new Error("Error signing up");
      }
      // window.location.href = 'login.html';
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async login(formid) {
    $(".formSpin").css("display", "inline-block").show();
    const formData = new FormData(document.getElementById(`${formid}`));
    try {
      const response = await axios.post("../api/index.php/auth", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $(".alert-danger").show();
        $(".text-danger").html(response.data.message);
        $(".formSpin").hide();
        $(".alert-success").hide();
        return;
      }
      if (response.data.statuscode === 0) {
        setItem("fullname", response.data.user.firstname + " " + response.data.user.lastname);
        setItem("status", response.data.user.status);
        setItem("email", response.data.user.email);
        setItem("username", response.data.user.username);
        setItem("userid", response.data.user.id);
        setItem("balance", response.data.user.balance);
        setItem("customerid", response.data.user.customerid);
        setItem("country", response.data.user.country);
        setItem("firstname", response.data.user.firstname);
        setItem("lastname", response.data.user.lastname);
        setItem("phone", response.data.user.phone);
        window.location.href = "../";
      } else if (response.data.statuscode === 5) {
        setItem("fullname", response.data.user.firstname + " " + response.data.user.lastname);
        setItem("status", response.data.user.status);
        setItem("email", response.data.user.email);
        setItem("username", response.data.user.username);
        setItem("userid", response.data.user.id);
        setItem("balance", response.data.user.balance);
        setItem("customerid", response.data.user.customerid);
        setItem("country", response.data.user.country);
        setItem("firstname", response.data.user.firstname);
        setItem("lastname", response.data.user.lastname);
        setItem("phone", response.data.user.phone);
        window.location.href = "../admin/";
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }
  async resetPassword() {
    $(".formSpin").css("display", "inline-block").show();
    const formData = new FormData(document.getElementById("resetform"));
    try {
      const response = await axios.post("../api/index.php/newpassword", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $(".alert-danger").show();
        $(".text-danger").html(response.data.message);
        $(".formSpin").hide();
        return;
      }
      if (response.data.statuscode === 0) {
        window.location.href = "sign-in.html?status=password";
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }
  async forgotPassword(email = null) {
    const formData = new FormData();
    if (email == null) {
      if ($("#remail").val() == "") {
        $(".alert-danger").show();
        $(".text-danger").html("Please provide your email");
        return;
      }
      $(".alert-danger").hide();
      $(".formSpin").css("display", "inline-block").show();
      formData.append("email", $("#remail").val());
    } else {
      formData.append("email", getItem("email"));
    }

    try {
      const response = await axios.post("../api/index.php/reset_password", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data.statuscode === -1) {
        $(".alert-danger").show();
        $(".text-danger").html(response.data.message);
        $(".formSpin").hide();
        return;
      }
      if (response.data.statuscode === 0) {
        setItem("email", $("#remail").val());
        window.location.href = "reset_password.html?status=successful";
        return;
      } else {
        throw new Error("Error signing up");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }
}

let auth = new Auth();
