/* global jQuery, ljatAdmin */
(function ($) {
  "use strict";

  var $doc = $(document);

  /* ------------------------------------------
     SAVE / UPDATE APPLICATION (form page)
     ------------------------------------------ */
  $doc.on("submit", "#ljat-app-form", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $btn = $("#ljat-save-btn");
    var data = {};

    $form.serializeArray().forEach(function (field) {
      data[field.name] = field.value;
    });

    $btn.prop("disabled", true).text(ljatAdmin.i18n.saving || "Saving...");

    $.post(
      ljatAdmin.ajaxUrl,
      {
        action: "ljat_save_application",
        nonce: ljatAdmin.nonce,
        data: data,
      },
      function (res) {
        if (res.success) {
          window.location.href = ljatAdmin.ajaxUrl
            .replace("admin-ajax.php", "admin.php?page=ljat-dashboard");
        } else {
          alert(res.data.message || ljatAdmin.i18n.error);
          $btn.prop("disabled", false).text("Save Application");
        }
      }
    ).fail(function () {
      alert(ljatAdmin.i18n.error);
      $btn.prop("disabled", false).text("Save Application");
    });
  });

  /* ------------------------------------------
     VIEW DETAIL (table eye icon)
     ------------------------------------------ */
  $doc.on("click", ".ljat-view-btn", function () {
    var id = $(this).data("id");
    var $modal = $("#ljat-detail-modal");
    var $content = $("#ljat-detail-content");

    $content.html("<p>Loading...</p>");
    $modal.addClass("active");

    $.get(
      ljatAdmin.ajaxUrl,
      {
        action: "ljat_get_application",
        nonce: ljatAdmin.nonce,
        id: id,
      },
      function (res) {
        if (res.success) {
          var item = res.data.item;
          $content.html(buildDetailHTML(item));

          // Store current id for edit/delete
          $modal.data("current-id", item.id);

          // Update edit link
          $("#ljat-detail-edit").attr(
            "href",
            ljatAdmin.ajaxUrl
              .replace("admin-ajax.php", "admin.php")
              .replace(
                /admin\.php/,
                "admin.php?page=ljat-add&id=" + item.id
              )
          );
        } else {
          $content.html("<p>" + (res.data.message || "Not found.") + "</p>");
        }
      }
    );
  });

  function buildDetailHTML(item) {
    var statusLabels = {
      saved: "Saved",
      applied: "Applied",
      interview: "Interview",
      offer: "Offer",
      rejected: "Rejected",
      withdrawn: "Withdrawn",
    };

    var dateApplied = item.date_applied
      ? new Date(item.date_applied).toLocaleDateString("en-US", {
          year: "numeric",
          month: "short",
          day: "2-digit",
        })
      : "--";

    var html =
      '<div class="jat-detail-section">' +
      "<h3>Job Information</h3>" +
      '<div class="jat-detail-row"><span class="label">Company</span><span class="value">' +
      esc(item.company) +
      "</span></div>" +
      '<div class="jat-detail-row"><span class="label">Role</span><span class="value">' +
      esc(item.role_title) +
      "</span></div>" +
      '<div class="jat-detail-row"><span class="label">Location</span><span class="value">' +
      esc(item.location || "--") +
      "</span></div>";

    if (item.job_url) {
      html +=
        '<div class="jat-detail-row"><span class="label">URL</span><span class="value"><a href="' +
        esc(item.job_url) +
        '" target="_blank">View Listing</a></span></div>';
    }

    if (item.salary_range) {
      html +=
        '<div class="jat-detail-row"><span class="label">Salary</span><span class="value">' +
        esc(item.salary_range) +
        "</span></div>";
    }

    html += "</div>";

    // Status section
    html +=
      '<div class="jat-detail-section">' +
      "<h3>Status</h3>" +
      '<div class="jat-detail-row"><span class="label">Current</span><span class="value">' +
      '<span class="jat-status jat-status-' +
      item.status +
      '"><span class="jat-status-dot"></span>' +
      (statusLabels[item.status] || item.status) +
      "</span></span></div>" +
      '<div class="jat-detail-row"><span class="label">Priority</span><span class="value">' +
      '<span class="jat-priority jat-priority-' +
      item.priority +
      '"><span class="jat-priority-dot"></span>' +
      item.priority.charAt(0).toUpperCase() + item.priority.slice(1) +
      "</span></span></div>" +
      '<div class="jat-detail-row"><span class="label">Date Applied</span><span class="value">' +
      dateApplied +
      "</span></div>" +
      "</div>";

    // Contact
    if (item.contact_name || item.contact_email) {
      html +=
        '<div class="jat-detail-section">' + "<h3>Contact</h3>";

      if (item.contact_name) {
        html +=
          '<div class="jat-detail-row"><span class="label">Name</span><span class="value">' +
          esc(item.contact_name) +
          "</span></div>";
      }
      if (item.contact_email) {
        html +=
          '<div class="jat-detail-row"><span class="label">Email</span><span class="value"><a href="mailto:' +
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
        '<div class="jat-detail-section">' +
        "<h3>Notes</h3>" +
        '<p style="font-size:0.875rem;color:#334155;">' +
        esc(item.notes) +
        "</p></div>";
    }

    return html;
  }

  function esc(str) {
    if (!str) return "";
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  /* ------------------------------------------
     CLOSE DETAIL MODAL
     ------------------------------------------ */
  $doc.on("click", ".ljat-close-detail", function () {
    $("#ljat-detail-modal").removeClass("active");
  });

  /* ------------------------------------------
     DELETE FROM DETAIL MODAL
     ------------------------------------------ */
  $doc.on("click", "#ljat-detail-delete", function () {
    var id = $("#ljat-detail-modal").data("current-id");
    if (!id) return;
    if (!confirm(ljatAdmin.i18n.confirmDelete)) return;

    $.post(
      ljatAdmin.ajaxUrl,
      {
        action: "ljat_delete_application",
        nonce: ljatAdmin.nonce,
        id: id,
      },
      function (res) {
        if (res.success) {
          window.location.reload();
        } else {
          alert(res.data.message || ljatAdmin.i18n.error);
        }
      }
    );
  });

  /* ------------------------------------------
     DELETE FROM TABLE ROW
     ------------------------------------------ */
  $doc.on("click", ".ljat-delete-btn", function () {
    var id = $(this).data("id");
    if (!confirm(ljatAdmin.i18n.confirmDelete)) return;

    $.post(
      ljatAdmin.ajaxUrl,
      {
        action: "ljat_delete_application",
        nonce: ljatAdmin.nonce,
        id: id,
      },
      function (res) {
        if (res.success) {
          window.location.reload();
        } else {
          alert(res.data.message || ljatAdmin.i18n.error);
        }
      }
    );
  });

  /* ------------------------------------------
     FILTER / SEARCH / PAGINATE (dashboard)
     ------------------------------------------ */
  var $tableBody = $("#ljat-table-body");
  var $tableWrapper = $("#ljat-table-wrapper");

  var statusLabels = {
    saved: "Saved",
    applied: "Applied",
    interview: "Interview",
    offer: "Offer",
    rejected: "Rejected",
    withdrawn: "Withdrawn",
  };

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
    var priority = item.priority
      ? item.priority.charAt(0).toUpperCase() + item.priority.slice(1)
      : "--";

    return (
      '<tr data-id="' + item.id + '">' +
      '<td class="font-semibold">' + esc(item.company) + "</td>" +
      "<td>" + esc(item.role_title) + "</td>" +
      "<td>" + esc(item.location || "--") + "</td>" +
      '<td><span class="jat-status jat-status-' + item.status + '"><span class="jat-status-dot"></span>' +
      (statusLabels[item.status] || item.status) + "</span></td>" +
      '<td><span class="jat-priority jat-priority-' + item.priority + '"><span class="jat-priority-dot"></span>' +
      priority + "</span></td>" +
      '<td class="text-muted">' + formatDate(item.date_applied) + "</td>" +
      '<td><div class="jat-table-actions">' +
      '<button class="jat-btn-icon jat-btn-sm ljat-view-btn dashicons dashicons-visibility" data-id="' +
      item.id + '" title="View"></button>' +
      '<a href="' + ljatAdmin.ajaxUrl.replace("admin-ajax.php", "admin.php?page=ljat-add&id=" + item.id) +
      '" class="jat-btn-icon jat-btn-sm dashicons dashicons-edit" title="Edit"></a>' +
      '<button class="jat-btn-icon jat-btn-sm ljat-delete-btn dashicons dashicons-trash" data-id="' +
      item.id + '" title="Delete"></button>' +
      "</div></td></tr>"
    );
  }

  function renderEmptyState() {
    return (
      '<tr class="ljat-empty-row"><td colspan="7"><div class="jat-empty-state">' +
      '<div class="jat-empty-state-icon dashicons dashicons-clipboard"></div>' +
      "<h3>No applications found</h3>" +
      "<p>Try adjusting your filters or add a new application.</p>" +
      "</div></td></tr>"
    );
  }

  function renderPagination(data) {
    $tableWrapper.find(".jat-pagination").remove();
    if (data.total_pages <= 1) return;

    var from = (data.page - 1) * data.per_page + 1;
    var to = Math.min(data.page * data.per_page, data.total);
    var html =
      '<div class="jat-pagination"><span>Showing ' + from + " - " + to +
      " of " + data.total + " applications</span>" +
      '<div class="jat-pagination-pages">';

    for (var i = 1; i <= data.total_pages; i++) {
      html +=
        '<a href="javascript:void(0)" class="jat-page-btn' +
        (data.page === i ? " active" : "") +
        '" data-page="' + i + '">' + i + "</a>";
    }

    html += "</div></div>";
    $tableWrapper.append(html);
  }

  function getFilterArgs() {
    return {
      action: "ljat_get_applications",
      nonce: ljatAdmin.nonce,
      status: $("#ljat-status-filter").val(),
      priority: $("#ljat-priority-filter").val(),
      search: $("#ljat-search").val(),
      per_page: 20,
    };
  }

  function loadApplications(page) {
    page = page || 1;
    var args = getFilterArgs();
    args.page = page;

    $tableBody.html(
      '<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">Loading...</td></tr>'
    );

    $.get(ljatAdmin.ajaxUrl, args, function (res) {
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

  $doc.on("click", "#ljat-apply-filter", function () {
    loadApplications(1);
  });

  $doc.on("click", "#ljat-reset-filter", function () {
    $("#ljat-search").val("");
    $("#ljat-status-filter").val("");
    $("#ljat-priority-filter").val("");
    loadApplications(1);
  });

  $doc.on("keydown", "#ljat-search", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      loadApplications(1);
    }
  });

  $doc.on("change", "#ljat-status-filter, #ljat-priority-filter", function () {
    loadApplications(1);
  });

  $doc.on("click", ".jat-page-btn", function (e) {
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
      '<div class="jat-toast jat-toast-' +
      type +
      '">' +
      '<span>' +
      (icons[type] || "") +
      "</span>" +
      "<span>" +
      message +
      "</span></div>";

    var $container = $("#ljat-toast-container");
    if (!$container.length) {
      $container = $('<div id="ljat-toast-container" class="jat-toast-container"></div>').appendTo("body");
    }

    var $toast = $(html).appendTo($container);
    setTimeout(function () {
      $toast.fadeOut(300, function () {
        $(this).remove();
      });
    }, 4000);
  }

  // Expose toast globally
  window.ljatToast = showToast;

})(jQuery);
