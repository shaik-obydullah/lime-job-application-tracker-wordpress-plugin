/* global jQuery, ojatAdmin, wp */
(function ($) {
  "use strict";

  var $doc = $(document);
  var i18n = wp.i18n;

  var statusLabels = {
    saved: i18n.__("Saved", "obydullah-job-application-tracker"),
    applied: i18n.__("Applied", "obydullah-job-application-tracker"),
    interview: i18n.__("Interview", "obydullah-job-application-tracker"),
    offer: i18n.__("Offer", "obydullah-job-application-tracker"),
    rejected: i18n.__("Rejected", "obydullah-job-application-tracker"),
    withdrawn: i18n.__("Withdrawn", "obydullah-job-application-tracker"),
  };

  function capitalize(str) {
    if (!str) return "--";
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function esc(str) {
    if (!str) return "";
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  /* ------------------------------------------
     SAVE / UPDATE APPLICATION (form page)
     ------------------------------------------ */
  $doc.on("submit", "#ojat-app-form", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $btn = $("#ojat-save-btn");
    var saveText = i18n.__("Save Application", "obydullah-job-application-tracker");
    var data = {};

    $form.serializeArray().forEach(function (field) {
      data[field.name] = field.value;
    });

    $btn.prop("disabled", true).text(i18n.__("Saving...", "obydullah-job-application-tracker"));

    $.post(
      ojatAdmin.ajaxUrl,
      {
        action: "ojat_save_application",
        nonce: ojatAdmin.nonce,
        data: data,
      },
      function (res) {
        if (res.success) {
          window.location.href = ojatAdmin.ajaxUrl
            .replace("admin-ajax.php", "admin.php?page=ojat-dashboard");
        } else {
          alert(res.data.message || i18n.__("Something went wrong. Please try again.", "obydullah-job-application-tracker"));
          $btn.prop("disabled", false).text(saveText);
        }
      }
    ).fail(function () {
      alert(i18n.__("Something went wrong. Please try again.", "obydullah-job-application-tracker"));
      $btn.prop("disabled", false).text(saveText);
    });
  });

  /* ------------------------------------------
     VIEW DETAIL (table eye icon)
     ------------------------------------------ */
  $doc.on("click", ".ojat-view-btn", function () {
    var id = $(this).data("id");
    var $modal = $("#ojat-detail-modal");
    var $content = $("#ojat-detail-content");

    $content.html("<p>" + i18n.__("Loading...", "obydullah-job-application-tracker") + "</p>");
    $modal.addClass("active");

    $.get(
      ojatAdmin.ajaxUrl,
      {
        action: "ojat_get_application",
        nonce: ojatAdmin.nonce,
        id: id,
      },
      function (res) {
        if (res.success) {
          var item = res.data.item;
          $content.html(buildDetailHTML(item));

          // Store current id for edit/delete
          $modal.data("current-id", item.id);

          // Update edit link
          $("#ojat-detail-edit").attr(
            "href",
            ojatAdmin.ajaxUrl
              .replace("admin-ajax.php", "admin.php")
              .replace(
                /admin\.php/,
                "admin.php?page=ojat-add&id=" + item.id
              )
          );
        } else {
          $content.html("<p>" + (res.data.message || i18n.__("Not found.", "obydullah-job-application-tracker")) + "</p>");
        }
      }
    );
  });

  function buildDetailHTML(item) {
    var dateApplied = item.date_applied
      ? new Date(item.date_applied).toLocaleDateString("en-US", {
          year: "numeric",
          month: "short",
          day: "2-digit",
        })
      : "--";

    var html =
      '<div class="ojat-detail-section">' +
      "<h3>" + i18n.__("Job Information", "obydullah-job-application-tracker") + "</h3>" +
      '<div class="ojat-detail-row"><span class="label">' + i18n.__("Company", "obydullah-job-application-tracker") + '</span><span class="value">' +
      esc(item.company) +
      "</span></div>" +
      '<div class="ojat-detail-row"><span class="label">' + i18n.__("Role", "obydullah-job-application-tracker") + '</span><span class="value">' +
      esc(item.role_title) +
      "</span></div>" +
      '<div class="ojat-detail-row"><span class="label">' + i18n.__("Location", "obydullah-job-application-tracker") + '</span><span class="value">' +
      esc(item.location || "--") +
      "</span></div>";

    if (item.job_url) {
      html +=
        '<div class="ojat-detail-row"><span class="label">URL</span><span class="value"><a href="' +
        esc(item.job_url) +
        '" target="_blank">' + i18n.__("View Listing", "obydullah-job-application-tracker") + "</a></span></div>";
    }

    if (item.salary_range) {
      html +=
        '<div class="ojat-detail-row"><span class="label">' + i18n.__("Salary", "obydullah-job-application-tracker") + '</span><span class="value">' +
        esc(item.salary_range) +
        "</span></div>";
    }

    html += "</div>";

    // Status section
    html +=
      '<div class="ojat-detail-section">' +
      "<h3>" + i18n.__("Status", "obydullah-job-application-tracker") + "</h3>" +
      '<div class="ojat-detail-row"><span class="label">' + i18n.__("Current", "obydullah-job-application-tracker") + '</span><span class="value">' +
      '<span class="ojat-status ojat-status-' +
      item.status +
      '"><span class="ojat-status-dot"></span>' +
      (statusLabels[item.status] || item.status) +
      "</span></span></div>" +
      '<div class="ojat-detail-row"><span class="label">' + i18n.__("Priority", "obydullah-job-application-tracker") + '</span><span class="value">' +
      '<span class="ojat-priority ojat-priority-' +
      item.priority +
      '"><span class="ojat-priority-dot"></span>' +
      capitalize(item.priority) +
      "</span></span></div>" +
      '<div class="ojat-detail-row"><span class="label">' + i18n.__("Date Applied", "obydullah-job-application-tracker") + '</span><span class="value">' +
      dateApplied +
      "</span></div>" +
      "</div>";

    // Contact
    if (item.contact_name || item.contact_email) {
      html +=
        '<div class="ojat-detail-section">' + "<h3>" + i18n.__("Contact", "obydullah-job-application-tracker") + "</h3>";

      if (item.contact_name) {
        html +=
          '<div class="ojat-detail-row"><span class="label">' + i18n.__("Name", "obydullah-job-application-tracker") + '</span><span class="value">' +
          esc(item.contact_name) +
          "</span></div>";
      }
      if (item.contact_email) {
        html +=
          '<div class="ojat-detail-row"><span class="label">' + i18n.__("Email", "obydullah-job-application-tracker") + '</span><span class="value"><a href="mailto:' +
          esc(item.contact_email) +
          '">' +
          esc(item.contact_email) +
          "</a></span></div>";
      }

      html += "</div>";
    }

    // Notes
    if (item.notes) {
      html +=
        '<div class="ojat-detail-section">' +
        "<h3>" + i18n.__("Notes", "obydullah-job-application-tracker") + "</h3>" +
        '<p style="font-size:0.875rem;color:#334155;">' +
        esc(item.notes) +
        "</p></div>";
    }

    return html;
  }

  /* ------------------------------------------
     CLOSE DETAIL MODAL
     ------------------------------------------ */
  $doc.on("click", ".ojat-close-detail", function () {
    $("#ojat-detail-modal").removeClass("active");
  });

  /* ------------------------------------------
     DELETE FROM DETAIL MODAL
     ------------------------------------------ */
  function confirmDelete() {
    return window.confirm(
      i18n.__("Are you sure you want to delete this application?", "obydullah-job-application-tracker")
    );
  }

  $doc.on("click", "#ojat-detail-delete", function () {
    var id = $("#ojat-detail-modal").data("current-id");
    if (!id) return;
    if (!confirmDelete()) return;

    $.post(
      ojatAdmin.ajaxUrl,
      {
        action: "ojat_delete_application",
        nonce: ojatAdmin.nonce,
        id: id,
      },
      function (res) {
        if (res.success) {
          window.location.reload();
        } else {
          alert(res.data.message || i18n.__("Something went wrong. Please try again.", "obydullah-job-application-tracker"));
        }
      }
    );
  });

  /* ------------------------------------------
     DELETE FROM TABLE ROW
     ------------------------------------------ */
  $doc.on("click", ".ojat-delete-btn", function () {
    var id = $(this).data("id");
    if (!confirmDelete()) return;

    $.post(
      ojatAdmin.ajaxUrl,
      {
        action: "ojat_delete_application",
        nonce: ojatAdmin.nonce,
        id: id,
      },
      function (res) {
        if (res.success) {
          window.location.reload();
        } else {
          alert(res.data.message || i18n.__("Something went wrong. Please try again.", "obydullah-job-application-tracker"));
        }
      }
    );
  });

  /* ------------------------------------------
     FILTER / SEARCH / PAGINATE (dashboard)
     ------------------------------------------ */
  var $tableBody = $("#ojat-table-body");
  var $tableWrapper = $("#ojat-table-wrapper");

  function formatDate(dateStr) {
    var d = new Date(dateStr);
    if (isNaN(d.getTime())) return "--";
    return d.toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "2-digit",
    });
  }

  function buildRow(item) {
    var priority = capitalize(item.priority);

    return (
      '<tr data-id="' + item.id + '">' +
      '<td class="font-semibold">' + esc(item.company) + "</td>" +
      "<td>" + esc(item.role_title) + "</td>" +
      "<td>" + esc(item.location || "--") + "</td>" +
      '<td><span class="ojat-status ojat-status-' + item.status + '"><span class="ojat-status-dot"></span>' +
      (statusLabels[item.status] || item.status) + "</span></td>" +
      '<td><span class="ojat-priority ojat-priority-' + item.priority + '"><span class="ojat-priority-dot"></span>' +
      priority + "</span></td>" +
      '<td class="text-muted">' + formatDate(item.date_applied) + "</td>" +
      '<td><div class="ojat-table-actions">' +
      '<button class="ojat-btn-icon ojat-btn-sm ojat-view-btn dashicons dashicons-visibility" data-id="' +
      item.id + '" title="' + i18n.__("View", "obydullah-job-application-tracker") + '"></button>' +
      '<a href="' + ojatAdmin.ajaxUrl.replace("admin-ajax.php", "admin.php?page=ojat-add&id=" + item.id) +
      '" class="ojat-btn-icon ojat-btn-sm dashicons dashicons-edit" title="' + i18n.__("Edit", "obydullah-job-application-tracker") + '"></a>' +
      '<button class="ojat-btn-icon ojat-btn-sm ojat-delete-btn dashicons dashicons-trash" data-id="' +
      item.id + '" title="' + i18n.__("Delete", "obydullah-job-application-tracker") + '"></button>' +
      "</div></td></tr>"
    );
  }

  function renderEmptyState() {
    return (
      '<tr class="ojat-empty-row"><td colspan="7"><div class="ojat-empty-state">' +
      '<div class="ojat-empty-state-icon dashicons dashicons-clipboard"></div>' +
      "<h3>" + i18n.__("No applications found", "obydullah-job-application-tracker") + "</h3>" +
      "<p>" + i18n.__("Try adjusting your filters or add a new application.", "obydullah-job-application-tracker") + "</p>" +
      "</div></td></tr>"
    );
  }

  function renderPagination(data) {
    $tableWrapper.find(".ojat-pagination").remove();
    if (data.total_pages <= 1) return;

    var from = (data.page - 1) * data.per_page + 1;
    var to = Math.min(data.page * data.per_page, data.total);
    var summary = i18n.sprintf(
      /* translators: 1: from count, 2: to count, 3: total count */
      i18n.__("Showing %1$d - %2$d of %3$d applications", "obydullah-job-application-tracker"),
      from,
      to,
      data.total
    );
    var html =
      '<div class="ojat-pagination"><span>' + summary + "</span>" +
      '<div class="ojat-pagination-pages">';

    for (var i = 1; i <= data.total_pages; i++) {
      html +=
        '<a href="javascript:void(0)" class="ojat-page-btn' +
        (data.page === i ? " active" : "") +
        '" data-page="' + i + '">' + i + "</a>";
    }

    html += "</div></div>";
    $tableWrapper.append(html);
  }

  function getFilterArgs() {
    return {
      action: "ojat_get_applications",
      nonce: ojatAdmin.nonce,
      status: $("#ojat-status-filter").val(),
      priority: $("#ojat-priority-filter").val(),
      search: $("#ojat-search").val(),
      per_page: 20,
    };
  }

  function loadApplications(page) {
    page = page || 1;
    var args = getFilterArgs();
    args.page = page;

    $tableBody.html(
      '<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">' +
      i18n.__("Loading...", "obydullah-job-application-tracker") +
      "</td></tr>"
    );

    $.get(ojatAdmin.ajaxUrl, args, function (res) {
      if (!res.success) {
        $tableBody.html(renderEmptyState());
        return;
      }

      var data = res.data;
      if (!data.items.length) {
        $tableBody.html(renderEmptyState());
      } else {
        var rows = [];
        $.each(data.items, function (i, item) {
          rows.push(buildRow(item));
        });
        $tableBody.html(rows.join(""));
      }

      renderPagination(data);
    });
  }

  $doc.on("click", "#ojat-apply-filter", function () {
    loadApplications(1);
  });

  $doc.on("click", "#ojat-reset-filter", function () {
    $("#ojat-search").val("");
    $("#ojat-status-filter").val("");
    $("#ojat-priority-filter").val("");
    loadApplications(1);
  });

  $doc.on("keydown", "#ojat-search", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      loadApplications(1);
    }
  });

  $doc.on("change", "#ojat-status-filter, #ojat-priority-filter", function () {
    loadApplications(1);
  });

  $doc.on("click", ".ojat-page-btn", function (e) {
    e.preventDefault();
    loadApplications($(this).data("page"));
  });

  /* ------------------------------------------
     TOAST NOTIFICATION
     ------------------------------------------ */
  function showToast(message, type) {
    type = type || "info";
    var icons = { success: "&#10004;", error: "&#10008;", info: "&#8505;" };
    var html =
      '<div class="ojat-toast ojat-toast-' +
      type +
      '">' +
      '<span>' +
      (icons[type] || "") +
      "</span>" +
      "<span>" +
      message +
      "</span></div>";

    var $container = $("#ojat-toast-container");
    if (!$container.length) {
      $container = $('<div id="ojat-toast-container" class="ojat-toast-container"></div>').appendTo("body");
    }

    var $toast = $(html).appendTo($container);
    setTimeout(function () {
      $toast.fadeOut(300, function () {
        $(this).remove();
      });
    }, 4000);
  }

  // Expose toast globally
  window.ojatToast = showToast;

})(jQuery);