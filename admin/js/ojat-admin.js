/* global jQuery, ojatAdmin, wp */
(function ($) {
  "use strict";

  var $doc = $(document);
  var i18n = wp.i18n;
  var cfg = window.ojatAdmin || {};


  // Vocabularies and formatting preferences are supplied by PHP so the
  // dashboard and the AJAX-rendered rows can never disagree.
  var statusLabels = cfg.statuses || {};
  var priorityLabels = cfg.priorities || {};

  function label(map, value) {
    return map[value] || value || "--";
  }

  function esc(str) {
    if (!str) return "";
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  function text(msg, fallback) {
    return msg || i18n.__(fallback, "obydullah-job-application-tracker");
  }

  function genericError() {
    return i18n.__(cfg.i18n.genericError || "Something went wrong. Please try again.", "obydullah-job-application-tracker");
  }

  function editUrl(id) {
    return cfg.editUrl + encodeURIComponent(id);
  }

  /* ------------------------------------------
     DATE FORMATTING
     ------------------------------------------ */
  function formatDate(value) {
    if (!value) return "--";

    var parts = String(value).split("-");
    if (parts.length !== 3) return value;

    var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    if (isNaN(d.getTime())) return "--";

    return d.toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "2-digit",
    });
  }

  /* ------------------------------------------
     TOAST NOTIFICATION
     ------------------------------------------ */
  function showToast(message, type) {
    type = type || "info";
    var icons = { success: "&#10004;", error: "&#10008;", info: "&#8505;" };

    var $container = $("#ojat-toast-container");
    if (!$container.length) {
      $container = $('<div id="ojat-toast-container" class="ojat-toast-container"></div>').appendTo("body");
    }

    var $toast = $(
      '<div class="ojat-toast ojat-toast-' + type + '">' +
        "<span>" + (icons[type] || "") + "</span>" +
        "<span>" + esc(message) + "</span></div>"
    ).appendTo($container);

    setTimeout(function () {
      $toast.fadeOut(300, function () {
        $(this).remove();
      });
    }, 4000);
  }

  /* ------------------------------------------
     SAVE / UPDATE APPLICATION (form page)
     ------------------------------------------ */
  $doc.on("submit", "#ojat-app-form", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $btn = $("#ojat-save-btn");
    var $isEdit = $form.find('input[name="id"]').length > 0;
    var restoreLabel = $isEdit
      ? i18n.__("Update Application", "obydullah-job-application-tracker")
      : i18n.__("Save Application", "obydullah-job-application-tracker");

    var data = {};
    $form.serializeArray().forEach(function (field) {
      data[field.name] = field.value;
    });

    $btn.prop("disabled", true).text(cfg.i18n.saving || i18n.__("Saving...", "obydullah-job-application-tracker"));

    $.post(cfg.ajaxUrl, {
      action: "ojat_save_application",
      nonce: cfg.nonces.save,
      data: data,
    })
      .done(function (res) {
        if (res && res.success) {
          window.location.href = cfg.adminUrl + "?page=ojat-dashboard";
          return;
        }
        showToast(text(res && res.data && res.data.message, "Something went wrong. Please try again."), "error");
        $btn.prop("disabled", false).text(restoreLabel);
      })
      .fail(function () {
        showToast(genericError(), "error");
        $btn.prop("disabled", false).text(restoreLabel);
      });
  });

  /* ------------------------------------------
     VIEW DETAIL (table eye icon)
     ------------------------------------------ */
  $doc.on("click", ".ojat-view-btn", function () {
    var id = $(this).data("id");
    var $modal = $("#ojat-detail-modal");
    var $content = $("#ojat-detail-content");

    $content.html("<p>" + esc(i18n.__(cfg.i18n.loading || "Loading...", "obydullah-job-application-tracker")) + "</p>");
    $modal.addClass("active");

    $.get(cfg.ajaxUrl, {
      action: "ojat_get_application",
      nonce: cfg.nonces.get,
      id: id,
    })
      .done(function (res) {
        if (res && res.success) {
          var item = res.data.item;
          $content.html(buildDetailHTML(item));
          $modal.data("current-id", item.id);
          $("#ojat-detail-edit").attr("href", editUrl(item.id));
          return;
        }
        $content.html("<p>" + esc(text(res && res.data && res.data.message, "Not found.")) + "</p>");
      })
      .fail(function () {
        $content.html("<p>" + esc(genericError()) + "</p>");
      });
  });

  function detailRow(key, value) {
    return (
      '<div class="ojat-detail-row"><span class="label">' + esc(key) +
      '</span><span class="value">' + value + "</span></div>"
    );
  }

  function section(title, inner) {
    return '<div class="ojat-detail-section"><h3>' + esc(title) + "</h3>" + inner + "</div>";
  }

  function badge(kind, value, text_) {
    return (
      '<span class="ojat-' + kind + " ojat-" + kind + "-" + esc(value) + '">' +
      '<span class="ojat-' + kind + '-dot"></span>' + esc(text_) + "</span>"
    );
  }

  function buildDetailHTML(item) {
    var job = "";

    job += detailRow(i18n.__("Company", "obydullah-job-application-tracker"), esc(item.company));
    job += detailRow(i18n.__("Role", "obydullah-job-application-tracker"), esc(item.role_title));
    job += detailRow(
      i18n.__("Location", "obydullah-job-application-tracker"),
      esc(item.location || "--")
    );

    if (item.job_url) {
      job += detailRow(
        "URL",
        '<a href="' + esc(item.job_url) + '" target="_blank" rel="noopener noreferrer">' +
          esc(i18n.__("View Listing", "obydullah-job-application-tracker")) + "</a>"
      );
    }

    if (item.salary_range) {
      job += detailRow(
        i18n.__("Salary", "obydullah-job-application-tracker"),
        esc(item.salary_range)
      );
    }

    var html = section(i18n.__("Job Information", "obydullah-job-application-tracker"), job);

    var status = "";
    status += detailRow(
      i18n.__("Current", "obydullah-job-application-tracker"),
      badge("status", item.status, label(statusLabels, item.status))
    );
    status += detailRow(
      i18n.__("Priority", "obydullah-job-application-tracker"),
      badge("priority", item.priority, label(priorityLabels, item.priority))
    );
    status += detailRow(
      i18n.__("Date Applied", "obydullah-job-application-tracker"),
      esc(formatDate(item.date_applied))
    );

    html += section(i18n.__("Status", "obydullah-job-application-tracker"), status);

    if (item.contact_name || item.contact_email) {
      var contact = "";

      if (item.contact_name) {
        contact += detailRow(
          i18n.__("Name", "obydullah-job-application-tracker"),
          esc(item.contact_name)
        );
      }

      if (item.contact_email) {
        contact += detailRow(
          i18n.__("Email", "obydullah-job-application-tracker"),
          '<a href="mailto:' + esc(item.contact_email) + '">' + esc(item.contact_email) + "</a>"
        );
      }

      html += section(i18n.__("Contact", "obydullah-job-application-tracker"), contact);
    }

    if (item.notes) {
      html += section(
        i18n.__("Notes", "obydullah-job-application-tracker"),
        '<p class="ojat-detail-notes">' + esc(item.notes) + "</p>"
      );
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
     DELETE
     ------------------------------------------ */
  function confirmDelete() {
    return window.confirm(
      i18n.__("Are you sure you want to delete this application?", "obydullah-job-application-tracker")
    );
  }

  function deleteApplication(id) {
    if (!id || !confirmDelete()) return;

    $.post(cfg.ajaxUrl, {
      action: "ojat_delete_application",
      nonce: cfg.nonces.delete,
      id: id,
    })
      .done(function (res) {
        if (res && res.success) {
          window.location.reload();
          return;
        }
        showToast(text(res && res.data && res.data.message, "Something went wrong. Please try again."), "error");
      })
      .fail(function () {
        showToast(genericError(), "error");
      });
  }

  $doc.on("click", "#ojat-detail-delete", function () {
    deleteApplication($("#ojat-detail-modal").data("current-id"));
  });

  $doc.on("click", ".ojat-delete-btn", function () {
    deleteApplication($(this).data("id"));
  });

  /* ------------------------------------------
     FILTER / SEARCH / PAGINATE (dashboard)
     ------------------------------------------ */
  var $tableBody = $("#ojat-table-body");
  var $tableWrapper = $("#ojat-table-wrapper");

  function buildRow(item) {
    return (
      '<tr data-id="' + esc(item.id) + '">' +
      '<td class="font-semibold">' + esc(item.company) + "</td>" +
      "<td>" + esc(item.role_title) + "</td>" +
      "<td>" + esc(item.location || "--") + "</td>" +
      "<td>" + badge("status", item.status, label(statusLabels, item.status)) + "</td>" +
      "<td>" + badge("priority", item.priority, label(priorityLabels, item.priority)) + "</td>" +
      '<td class="text-muted">' + esc(formatDate(item.date_applied)) + "</td>" +
      '<td><div class="ojat-table-actions">' +
      '<button class="ojat-btn-icon ojat-btn-sm ojat-view-btn dashicons dashicons-visibility" data-id="' +
      esc(item.id) + '" title="' + esc(i18n.__("View", "obydullah-job-application-tracker")) + '"></button>' +
      '<a href="' + esc(editUrl(item.id)) +
      '" class="ojat-btn-icon ojat-btn-sm dashicons dashicons-edit" title="' +
      esc(i18n.__("Edit", "obydullah-job-application-tracker")) + '"></a>' +
      '<button class="ojat-btn-icon ojat-btn-sm ojat-delete-btn dashicons dashicons-trash" data-id="' +
      esc(item.id) + '" title="' + esc(i18n.__("Delete", "obydullah-job-application-tracker")) + '"></button>' +
      "</div></td></tr>"
    );
  }

  function renderEmptyState() {
    return (
      '<tr class="ojat-empty-row"><td colspan="7"><div class="ojat-empty-state">' +
      '<div class="ojat-empty-state-icon dashicons dashicons-clipboard"></div>' +
      "<h3>" + esc(i18n.__("No applications found", "obydullah-job-application-tracker")) + "</h3>" +
      "<p>" + esc(i18n.__("Try adjusting your filters or add a new application.", "obydullah-job-application-tracker")) + "</p>" +
      "</div></td></tr>"
    );
  }

  function renderLoadingState() {
    return (
      '<tr class="ojat-loading-row"><td colspan="7" class="ojat-loading-cell">' +
      esc(i18n.__(cfg.i18n.loading || "Loading...", "obydullah-job-application-tracker")) +
      "</td></tr>"
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
      '<div class="ojat-pagination"><span>' + esc(summary) + "</span>" +
      '<div class="ojat-pagination-pages">';

    for (var i = 1; i <= data.total_pages; i++) {
      html +=
        '<a href="#" class="ojat-page-btn' + (data.page === i ? " active" : "") +
        '" data-page="' + i + '">' + i + "</a>";
    }

    html += "</div></div>";
    $tableWrapper.append(html);
  }

  function getFilterArgs() {
    return {
      action: "ojat_get_applications",
      nonce: cfg.nonces.list,
      status: $("#ojat-status-filter").val() || "",
      priority: $("#ojat-priority-filter").val() || "",
      search: $("#ojat-search").val() || "",
      per_page: cfg.perPage || 20,
    };
  }

  function loadApplications(page) {
    if (!$tableBody.length) return;

    var args = getFilterArgs();
    args.page = page || 1;

    $tableBody.html(renderLoadingState());

    $.get(cfg.ajaxUrl, args)
      .done(function (res) {
        if (!res || !res.success) {
          $tableBody.html(renderEmptyState());
          return;
        }

        var data = res.data;

        if (!data.items.length) {
          $tableBody.html(renderEmptyState());
        } else {
          $tableBody.html(
            data.items
              .map(function (item) {
                return buildRow(item);
              })
              .join("")
          );
        }

        renderPagination(data);
      })
      .fail(function () {
        $tableBody.html(renderEmptyState());
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

  window.ojatToast = showToast;
})(jQuery);
