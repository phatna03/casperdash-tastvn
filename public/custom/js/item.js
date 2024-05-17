//restaurant
function restaurant_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/restaurant/store', {
    name: form.find('input[name=name]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();

    })
    .catch(error => {
      var restoreModal = false;

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          if (v == 'can_restored') {
            restoreModal = true;
          } else {
            message_from_toast('error', acmcfs.message_title_error, v);
          }
        });
      }

      // if (restoreModal && $('#modal_restore_item').length) {
      //   $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
      //   $('#modal_restore_item .alert').empty()
      //     .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
      //   $('#modal_restore_item').modal('show');
      // }
    })
    .then(() => {

      form_loading(form, false);

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}
function restaurant_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function restaurant_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/restaurant/update', {
    item: form.find('input[name=item]').val(),
    name: form.find('input[name=name]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {
      var restoreModal = false;

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          if (v == 'can_restored') {
            restoreModal = true;
          } else {
            message_from_toast('error', acmcfs.message_title_error, v);
          }
        });
      }

      // if (restoreModal && $('#modal_restore_item').length) {
      //   $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
      //   $('#modal_restore_item .alert').empty()
      //     .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
      //   $('#modal_restore_item').modal('show');
      // }
    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
function restaurant_delete_prepare(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_item');

  popup.find('input[name=item]').val(tr.attr('data-id'));
}
function restaurant_delete(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/restaurant/delete', {
    item: popup.find('input[name=item]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function restaurant_restore(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/restaurant/restore', {
    item: popup.find('input[name=item]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function restaurant_info(itd) {
  var popup = $('#modal_info_item');

  popup.find('input[name=restaurant_parent_id]').val(itd);

  popup.find('.modal-header h4').text('Loading...');
  popup.find('.modal-body').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/restaurant/info', {
    item: itd,
  })
    .then(response => {

      var title = '[Restaurant] ' + response.data.restaurant.name;
      popup.find('.modal-header h4').empty().append(title);

      popup.find('.modal-body').removeClass('text-center').empty()
        .append(response.data.html);

      bind_datad(popup);
      popup.modal('show');

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });

  return false;
}
function restaurant_food_import_prepare(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_food_import');

  popup.find('input[name=restaurant_parent_id]').val(tr.attr('data-id'));
}
function restaurant_food_import(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  const formData = new FormData();
  formData.append('excel', form.find('input[type=file]')[0].files[0]);
  formData.append('restaurant_parent_id', form.find('input[name=restaurant_parent_id]').val());

  axios.post('/admin/restaurant/food/import', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
function restaurant_food_remove_prepare(ele) {
  var food_item = $(ele).closest('.data_food_item');
  var popup1 = $(ele).closest('.modal');
  var popup2 = $('#modal_food_remove');

  popup2.find('input[name=restaurant_parent_id]').val(popup1.find('input[name=restaurant_parent_id]').val());
  popup2.find('input[name=food_id]').val(food_item.attr('data-food_id'));

  popup2.modal('show');
}
function restaurant_food_remove() {
  var popup1 = $('#modal_info_item');
  var popup2 = $('#modal_food_remove');
  form_loading(popup2);

  axios.post('/admin/restaurant/food/remove', {
    restaurant_parent_id: popup2.find('input[name=restaurant_parent_id]').val(),
    food_id: popup2.find('input[name=food_id]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      popup1.find('.data_food_item_' + popup2.find('input[name=food_id]').val()).remove();

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    })
    .then(() => {

      form_loading(popup2, false);
      form_close(popup2);
    });

  return false;
}
function restaurant_food_live_group(ele) {
  var live_group = $(ele).val();
  var food_item = $(ele).closest('.data_food_item');
  var popup = $(ele).closest('.modal');

  if (!live_group || !parseInt(live_group)) {
    return false;
  }

  axios.post('/admin/restaurant/food/group', {
    restaurant_parent_id: popup.find('input[name=restaurant_parent_id]').val(),
    food_id: food_item.attr('data-food_id'),
    live_group: live_group,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });

  return false;
}
//sensor
function sensor_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/sensor/store', {
    name: form.find('input[name=name]').val(),
    s3_bucket_name: form.find('input[name=s3_bucket_name]').val(),
    s3_bucket_address: form.find('input[name=s3_bucket_address]').val(),
    rbf_scan: form.find('input[name=rbf_scan]').is(':checked') ? 1 : 0,
    restaurant_parent_id: form.find('select[name=restaurant]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();

    })
    .catch(error => {
      var restoreModal = false;

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          if (v == 'can_restored') {
            restoreModal = true;
          } else {
            message_from_toast('error', acmcfs.message_title_error, v);
          }
        });
      }

      // if (restoreModal && $('#modal_restore_item').length) {
      //   $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
      //   $('#modal_restore_item .alert').empty()
      //     .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
      //   $('#modal_restore_item').modal('show');
      // }

    })
    .then(() => {

      form_loading(form, false);

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}
function sensor_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));
  form.find('input[name=s3_bucket_name]').val(tr.attr('data-s3_bucket_name'));
  form.find('input[name=s3_bucket_address]').val(tr.attr('data-s3_bucket_address'));

  form.find('select[name=restaurant]').selectize()[0].selectize.setValue(tr.attr('data-restaurant_parent_id'));

  form.find('input[name=rbf_scan]').prop('checked', false);
  if (parseInt(tr.attr('data-rbf_scan'))) {
    form.find('input[name=rbf_scan]').prop('checked', true);
  }

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function sensor_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/sensor/update', {
    item: form.find('input[name=item]').val(),
    name: form.find('input[name=name]').val(),
    s3_bucket_name: form.find('input[name=s3_bucket_name]').val(),
    s3_bucket_address: form.find('input[name=s3_bucket_address]').val(),
    rbf_scan: form.find('input[name=rbf_scan]').is(':checked') ? 1 : 0,
    restaurant_parent_id: form.find('select[name=restaurant]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {
      var restoreModal = false;

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          if (v == 'can_restored') {
            restoreModal = true;
          } else {
            message_from_toast('error', acmcfs.message_title_error, v);
          }
        });
      }

      // if (restoreModal && $('#modal_restore_item').length) {
      //   $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
      //   $('#modal_restore_item .alert').empty()
      //     .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
      //   $('#modal_restore_item').modal('show');
      // }

    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
function sensor_delete_prepare(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_item');

  popup.find('input[name=item]').val(tr.attr('data-id'));
}
function sensor_delete(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/sensor/delete', {
    item: popup.find('input[name=item]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function sensor_restore(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/sensor/restore', {
    item: popup.find('input[name=item]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function sensor_info(itd) {
  page_url(acmcfs.link_base_url + '/admin/sensor/info/' + itd);
}
function sensor_stats() {
  var wrap = $('#wrap-stats-total');

  var times = wrap.find('input[name=search_time]').val();

  wrap.find('.wrap-search-condition').addClass('d-none');
  if (times && times !== '') {
    wrap.find('.wrap-search-condition').removeClass('d-none');
    wrap.find('.wrap-search-condition .search-time').empty().text(times);
  }

  axios.post('/admin/sensor/stats', {
    item: $('body input[name=current_restaurant]').val(),
    times: times,
    type: 'total',
  })
    .then(response => {

      // console.log(response.data.stats.sql1);

      wrap.find('.stats-total-found-count').text(response.data.stats.total_found);

      wrap.find('.stats-today-found .fnumber').text(response.data.stats.today_found);
      wrap.find('.stats-today-found').removeClass('d-none');
      if (times && times !== '') {
        wrap.find('.stats-today-found').addClass('d-none');
      }

      wrap.find('.stats-food-category-count').text(response.data.stats.category_error);
      wrap.find('.stats-food-category-percent').text(response.data.stats.category_error_percent > 0
        ? '(' + response.data.stats.category_error_percent + '%)' : '');

      wrap.find('.stats-food-category-list').addClass('d-none');
      if (response.data.stats.category_error_list.length) {
        var html = '';

        response.data.stats.category_error_list.forEach(function (v, k) {
          var title = v.food_category_name && v.food_category_name !== '' && v.food_category_name !== 'null'
            ? v.food_category_name : 'Not group food category yet';
          html += '<li><a class="dropdown-item" href="javascript:void(0);">' + title + '</a></li>';
        });

        wrap.find('.stats-food-category-list').empty()
          .removeClass('d-none').append(html);
      }

      wrap.find('.stats-food-count').text(response.data.stats.food_error);
      wrap.find('.stats-food-percent').text(response.data.stats.food_error_percent > 0
        ? '(' + response.data.stats.food_error_percent + '%)' : '');

      wrap.find('.stats-food-list').addClass('d-none');
      if (response.data.stats.food_error_list.length) {
        var html = '';

        response.data.stats.food_error_list.forEach(function (v, k) {
          var title = v.food_name && v.food_name !== '' && v.food_name !== 'null'
            ? v.food_name : 'No food found';
          html += '<li><a class="dropdown-item" href="javascript:void(0);"><b class="acm-mr-px-5">' + v.total_error + '</b>' + v.food_name + '</a></li>';
        });

        wrap.find('.stats-food-list').empty()
          .removeClass('d-none').append(html);
      }

      wrap.find('.stats-ingredients-missing-count').text(response.data.stats.ingredient_missing);
      wrap.find('.stats-ingredients-missing-percent').text(response.data.stats.ingredient_missing_percent > 0
        ? '(' + response.data.stats.ingredient_missing_percent + '%)' : '');

      wrap.find('.stats-ingredients-missing-list').addClass('d-none');
      if (response.data.stats.ingredient_missing_list.length) {
        var html = '';

        response.data.stats.ingredient_missing_list.forEach(function (v, k) {
          var title = v.ingredient_name && v.ingredient_name !== '' && v.ingredient_name !== 'null'
            ? v.ingredient_name : 'No ingredient found';
          html += '<li><a class="dropdown-item" href="javascript:void(0);"><b class="acm-mr-px-5">' + v.total_error + '</b>' + v.ingredient_name + '</a></li>';
        });

        wrap.find('.stats-ingredients-missing-list').empty()
          .removeClass('d-none').append(html);
      }

      wrap.find('.stats-time-frames-count').text(response.data.stats.time_frame);

      wrap.find('.stats-time-frames-list').addClass('d-none');
      if (response.data.stats.time_frame_list.length) {
        var html = '';

        response.data.stats.time_frame_list.forEach(function (v, k) {
          var title = v.hour_error < 10
            ? '0' + v.hour_error + ':00 - ' + '0' + v.hour_error + ':59'
            : v.hour_error + ':00 - ' + v.hour_error + ':59';
          html += '<li><a class="dropdown-item" href="javascript:void(0);"><b class="acm-mr-px-5">' + v.total_error + '</b>' + title + '</a></li>';
        });

        wrap.find('.stats-time-frames-list').empty()
          .removeClass('d-none').append(html);
      }

      bind_datad(wrap);

    })
    .catch(error => {

    });
}
function sensor_stats_clear(ele, type) {
  var wrap = $(ele).closest('.wrap-stats');

  wrap.find('input[name=search_time]').val('').trigger('change');
}
function sensor_delete_food_scan_prepare(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_item');

  popup.find('input[name=item]').val(tr.attr('data-itd'));
}
function sensor_delete_food_scan(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/sensor/food/scan/delete', {
    item: popup.find('input[name=item]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      if (typeof datatable_listing_scan_refresh() !== "undefined") {
        datatable_listing_scan_refresh();
      }

    })
    .catch(error => {

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function sensor_food_scan_update_prepare() {
  var popup = $('#modal_food_scan_info_update');
  var view_current = parseInt($('body input[name=popup_view_id_itm]').val());

  axios.post('/admin/sensor/food/scan/get', {
    item: view_current,
  })
    .then(response => {

      popup.find('.modal-body').empty()
        .append(response.data.html_info);

      bind_datad(popup);
      popup.modal('show');

      setTimeout(function () {
        popup.find('#user-update-food select').attr('onchange', 'sensor_food_scan_update_select(this)');
      }, acmcfs.timeout_default);

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });
}
function sensor_food_scan_update(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var view_current = parseInt($('body input[name=popup_view_id_itm]').val());

  form_loading(form);

  var missings = [];
  if (form.find('.wrap-ingredients .js-item-row').length) {
    form.find('.wrap-ingredients .js-item-row').each(function (k, v) {
      var tr = $(v);
      missings.push({
        id: tr.attr('data-itd'),
        type: tr.attr('data-ingredient_type'),
        quantity: input_number_only(tr.find('input[name=quantity]').val()),
      });
    });
  }

  var texts = [];
  if (form.find('.wrap-texts .itm-text').length) {
    form.find('.wrap-texts .itm-text').each(function (k, v) {
      var tr = $(v);
      if (tr.find('input').is(':checked')) {
        texts.push(tr.find('input').attr('data-itd'));
      }
    });
  }

  axios.post('/admin/sensor/food/scan/update', {
    item: view_current,
    note: form.find('textarea[name=update_note]').val(),
    food: form.find('select[name=update_food]').val(),
    missings: missings,
    texts: texts,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);
      sensor_food_scan_info_rebind(view_current);

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
function sensor_food_scan_update_select(ele) {
  var bind = $(ele);
  var form = bind.closest('form');
  var chosen = bind.val();
  if (chosen && parseInt(chosen)) {

  } else {
    form.find('.wrap-ingredients').addClass('d-none')
    return false;
  }

  axios.post('/admin/food/get', {
    item: chosen,
  })
    .then(response => {

      form.find('.wrap-ingredients').removeClass('d-none')
        .empty()
        .append(response.data.html_scan_update);
      bind_datad(form);

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });

  return false;
}
function sensor_food_scan_api(ele, type) {
  var bind = $(ele);
  var tr = bind.closest('tr');

  bind.find('.ic_current').addClass('d-none');
  bind.append('<i class="mdi mdi-reload ic_loading"></i>');

  axios.post('/admin/sensor/food/scan/api', {
    item: tr.attr('data-itd'),
    type: type,
  })
    .then(response => {

      bind.find('.ic_current').removeClass('d-none');
      bind.find('.ic_loading').remove();

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });

  return false;
}
function sensor_food_scan_info(id) {
  var popup = $('#modal_food_scan_info');
  popup.find('input[name=popup_view_id_itm]').val(id);

  var hidden_btns = true;
  var table1 = $('#datatable-listing-scan table');
  if (table1.length) {
    var count = 0;
    var ids = '';
    if (table1.find('tbody tr').length) {
      table1.find('tbody tr').each(function (k, v) {
        if (parseInt($(v).attr('data-itd'))) {
          ids += parseInt($(v).attr('data-itd')) + ';';
          count++;
        }
      });
    }

    if (ids && ids != '') {
      popup.find('input[name=popup_view_ids]').val(ids);
    }
    if (count > 1) {
      hidden_btns = false;
    }
  }

  var table2 = $('#wrap-notifications');
  if (table2.length) {
    var count = 0;
    var ids = '';
    if (table2.find('.acm-itm-notify').length) {
      table2.find('.acm-itm-notify').each(function (k, v) {
        if (parseInt($(v).attr('data-rfs-id'))) {
          ids += parseInt($(v).attr('data-rfs-id')) + ';';
          count++;
        }
      });
    }

    if (ids && ids != '') {
      popup.find('input[name=popup_view_ids]').val(ids);
    }
    if (count > 1) {
      hidden_btns = false;
    }
  }

  popup.find('.acm-modal-arrow').removeClass('d-none');
  if (hidden_btns) {
    popup.find('.acm-modal-arrow').addClass('d-none');
  }

  popup.find('.modal-header h4').text('Loading...');
  popup.find('.modal-body').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/sensor/food/scan/info', {
    item: id,
  })
    .then(response => {

      var title = response.data.restaurant.name + ' <span class="badge acm-ml-px-10 bg-primary">ID: ' + response.data.item.id + '</span>';
      popup.find('.modal-header h4').empty().append(title);

      popup.find('.modal-body').removeClass('text-center').empty()
        .append(response.data.html_info);

      bind_datad(popup);
      popup.modal('show');



    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });

  return false;
}
function sensor_food_scan_info_action(next = 0) {
  var popup = $('#modal_food_scan_info');
  var arr = popup.find('input[name=popup_view_ids]').val().split(';').filter(Boolean);
  var view_current = parseInt(popup.find('input[name=popup_view_id_itm]').val());
  var view_next = 0;

  if (arr.length) {
    if (next) {
      for (var i = 0; i < arr.length; ++i) {
        if (parseInt(arr[i]) == view_current) {
          if (arr[i + 1]) {
            view_next = arr[i + 1];
          } else {
            view_next = arr[0];
          }
        }
      }
    } else {
      for (var i = 0; i < arr.length; ++i) {
        if (parseInt(arr[i]) == view_current) {
          if (arr[i - 1]) {
            view_next = arr[i - 1];
          } else {
            view_next = arr[arr.length - 1];
          }
        }
      }
    }

    popup.find('input[name=popup_view_id_itm]').val(view_next);

    //rebind
    sensor_food_scan_info_rebind(view_next);
  }
}
function sensor_food_scan_info_rebind(id) {
  var popup = $('#modal_food_scan_info');

  popup.find('.modal-body').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/sensor/food/scan/info', {
    item: id,
  })
    .then(response => {

      var title = response.data.restaurant.name + ' <span class="badge acm-ml-px-10 bg-primary">ID: ' + response.data.item.id + '</span>';
      popup.find('.modal-header h4').empty().append(title);

      popup.find('.modal-body').removeClass('text-center').empty()
        .append(response.data.html_info);

      bind_datad(popup);

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });

  return false;
}
function sensor_food_scan_error_info(ele) {
  var tr = $(ele);
  var popup = $('#modal_food_scan_error');

  popup.find('.modal-header h4').text('Loading...');
  popup.find('.modal-body').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/sensor/food/scan/error', {
    item: tr.attr('data-restaurant_id'),
    food: tr.attr('data-food_id'),
    missing_ids: tr.attr('data-missing_ids'),
    time_upload: $('#datatable-listing-error .wrap-search-form form input[name=time_upload]').val(),
    time_scan: $('#datatable-listing-error .wrap-search-form form input[name=time_scan]').val(),
  })
    .then(response => {

      popup.find('.modal-header h4').text(response.data.restaurant.name);
      popup.find('.modal-body').removeClass('text-center').empty()
        .append(response.data.html_info);

      bind_datad(popup);
      popup.modal('show');

    })
    .catch(error => {

    });

  return false;
}
function sensor_retraining() {
  var popup = $('#modal_roboflow_retraining');
  var tbl = $('#datatable-listing-scan');
  var ids = [];

  form_loading(popup);

  if (tbl.find('table tbody tr').length) {
    tbl.find('table tbody tr').each(function (k, v) {
      var bind = $(v);
      var itd = parseInt(bind.attr('data-itd'));

      if (itd) {
        ids.push(itd);
      }
    });
  }

  if (!ids.length) {
    message_from_toast('error', acmcfs.message_title_error, 'Item not found');
    return false;
  }

  axios.post('/admin/roboflow/retraining', {
    items: ids,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);

      if (typeof datatable_listing_scan_refresh !== "undefined") {
        datatable_listing_scan_refresh();
      }

    })
    .catch(error => {

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });
}
function sensor_search_food_scan(ele) {
  var form = $(ele).closest('form');
  if (form.length) {
    setTimeout(function () {
      if (typeof datatable_listing_scan_refresh !== "undefined") {
        datatable_listing_scan_refresh();
      }
    }, acmcfs.timeout_default);
  }
}
function sensor_search_food_scan_error(ele) {
  var form = $(ele).closest('form');
  if (form.length) {
    setTimeout(function () {
      if (typeof datatable_listing_error_refresh !== "undefined") {
        datatable_listing_error_refresh();
      }
    }, acmcfs.timeout_default);
  }
}

