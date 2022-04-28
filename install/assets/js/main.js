var onFormSubmit = function ($form) {
  $form.find('[type="submit"]').attr('disabled', 'disabled').find(".loader").removeClass("hide");
  $form.find('[type="submit"]').find(".button-text").addClass("hide");
  $("#alert-container").html("");
};
var onSubmitSussess = function ($form) {
  $form.find('[type="submit"]').removeAttr('disabled').find(".loader").addClass("hide");
  $form.find('[type="submit"]').find(".button-text").removeClass("hide");
};

$(document).ready(function () {
  var $preInstallationTab = $("#pre-installation-tab"),
  $configurationTab = $("#configuration-tab");

  $(".form-next").click(function () {
    if ($preInstallationTab.hasClass("active")) {
      $preInstallationTab.removeClass("active");
      $configurationTab.addClass("active");
      $("#pre-installation").find("i").removeClass("fa-circle-o").addClass("fa-check-circle");
      $("#configuration").addClass("active");
      $("#host").focus();
    }
  });

  $("#config-form").submit(function () {
    var $form = $(this);
    onFormSubmit($form);
    $form.ajaxSubmit({
      dataType: "json",
      success: function (result) {
        onSubmitSussess($form, result);
        if (result.success) {
          $configurationTab.removeClass("active");
          $("#configuration").find("i").removeClass("fa-circle-o").addClass("fa-check-circle");
          $("#finished").find("i").removeClass("fa-circle-o").addClass("fa-check-circle");
          $("#finished").addClass("active");
          $("#finished-tab").addClass("active");
        } else {
          $("#alert-container").html('<div class="alert alert-danger" role="alert">' + result.message + '</div>');
        }
      }
    });
    return false;
  });

});
