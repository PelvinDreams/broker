class Main {
  async getPackages() {
    try {
      const response = await axios.get("http://localhost/astrofx/api/index.php/getpackages", {});

      if (response.data.statuscode == 0) {
        console.log(response.data);
        var element = "";
        response.data.plans.forEach(function (item) {
          element += `<div class="col-lg-3 col-sm-6 col-md-6">
                        <div class="plan__item">
                            <div class="plan__item-header">
                                <div class="left">
                                    <h5 class="title planName">${item.name}</h5>
                                    <span>Platinum</span>
                                </div>
                                <div class="right">
                                    <h5 class="title">
                                        <span id="percentage">${item.percentage}</span>
                                        %
                                    </h5>
                                    <span>Return</span>
                                </div>
                            </div>
                            <div class="plan__item-body">
                                <ul>
                                    <li>
                                        <span class="name">Profit</span>
                                        <span class="info">
                                            Lifetime
                                        </span>
                                    </li>

                                    <li>
                                        <span class="name me-1">Capital will be back</span>
                                        <span class="badge align-self-center me-auto bg--primary">Yes</span>
                                    </li>

                                    <li>
                                        <span class="name 1">Repeatable</span>
                                        <span class="badge align-self-center me-auto bg--danger">NO</span>
                                    </li>
                                </ul>

                                <h6 class="text-center amount-range">$${item.minimum} - $${item.maximum}</h6>
                                <button class="cmn--btn w-100 invest-plan" type="button" data-bs-toggle="modal"
                                    data-bs-target="#invest-modal" data-title="Platinum plan" data-id="17" data-type="0"
                                    data-fixAmount="0">
                                    Invest Now </button>
                            </div>
                        </div>
                    </div>
                    `;
        });
        $(".listPackages").html(element);
      }
    } catch (e) {
      console.log(e.message);
    }
  }
}

let main = new Main();
