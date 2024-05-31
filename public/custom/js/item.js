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
function sensor_kitchen(itd) {
  page_url(acmcfs.link_base_url + '/admin/kitchen/' + itd);
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

  var view_current = parseInt($('body input[name=popup_view_id_itm]').val());

  axios.post('/admin/sensor/food/scan/get/food', {
    food: chosen,
    rfs: view_current,
  })
    .then(response => {

      form.find('.wrap-ingredients').removeClass('d-none')
        .empty()
        .append(response.data.html);
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
//food
function food_ingredient_core_quick(ele, itd) {
  var bind = $(ele);
  var wrap = bind.closest('.wrap_text_roboflow_ingredient');

  if (wrap.hasClass('cored')) {
    wrap.removeClass('cored').removeClass('text-danger')
      .addClass('text-dark');
  } else {
    wrap.addClass('cored').addClass('text-danger')
      .removeClass('text-dark');
  }

  axios.post('/admin/restaurant/food/core', {
    item: itd,
  })
    .then(response => {
      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);
    })
    .catch(error => {
      console.log(error);
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    })
    .then(() => {

    });

  return false;
}
//ingredient
function ingredient_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/ingredient/store', {
    name: form.find('input[name=name]').val(),
    name_vi: form.find('input[name=name_vi]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();
      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    })
    .catch(error => {
      var restoreModal = false;

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          // if (v == 'can_restored') {
          //   restoreModal = true;
          // } else {
          message_from_toast('error', acmcfs.message_title_error, v);
          // }
        });
      }

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

    })
    .then(() => {

      form_loading(form, false);

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}
function ingredient_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));
  form.find('input[name=name_vi]').val(tr.attr('data-name_vi'));

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function ingredient_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/ingredient/update', {
    item: form.find('input[name=item]').val(),
    name: form.find('input[name=name]').val(),
    name_vi: form.find('input[name=name_vi]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

    })
    .catch(error => {
      var restoreModal = false;

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          // if (v == 'can_restored') {
          //   restoreModal = true;
          // } else {
          message_from_toast('error', acmcfs.message_title_error, v);
          // }
        });
      }

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
//text
function text_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/text/store', {
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
          // if (v == 'can_restored') {
          //   restoreModal = true;
          // } else {
          message_from_toast('error', acmcfs.message_title_error, v);
          // }
        });
      }

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has text: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    })
    .then(() => {

      form_loading(form, false);

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}
function text_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function text_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/text/update', {
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
          // if (v == 'can_restored') {
          //   restoreModal = true;
          // } else {
          message_from_toast('error', acmcfs.message_title_error, v);
          // }
        });
      }

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
//setting
function sys_setting_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_item');
  popup.modal('show');
  return false;
}
function sys_setting() {
  var popup = $('#modal_confirm_item');
  var form = $('#frm-settings');
  form_loading(popup);

  axios.post('/admin/setting/update', {
    s3_region: form.find('input[name=s3_region]').val(),
    s3_api_key: form.find('input[name=s3_api_key]').val(),
    s3_api_secret: form.find('input[name=s3_api_secret]').val(),
    rbf_api_key: form.find('input[name=rbf_api_key]').val(),
    rbf_dataset_scan: form.find('input[name=rbf_dataset_scan]').val(),
    rbf_dataset_ver: form.find('input[name=rbf_dataset_ver]').val(),
    mail_mailer: form.find('input[name=mail_mailer]').val(),
    mail_host: form.find('input[name=mail_host]').val(),
    mail_username: form.find('input[name=mail_username]').val(),
    mail_password: form.find('input[name=mail_password]').val(),
    mail_port: form.find('input[name=mail_port]').val(),
    mail_encryption: form.find('input[name=mail_encryption]').val(),
    mail_from_address: form.find('input[name=mail_from_address]').val(),
    mail_from_name: form.find('input[name=mail_from_name]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);
      page_reload(acmcfs.timeout_quick);

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
    });

  return false;
}
//user
function user_clear(frm) {
  var form = $(frm);

  form.find('input[name=name]').val('');
  form.find('input[name=email]').val('');
  form.find('input[name=phone]').val('');
  form.find('input[name=status][value=active]').prop('checked', true);
  form.find('input[name=role][value=user]').prop('checked', true);
  form.find('textarea[name=note]').val('');
}
function user_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/user/store', {
    name: form.find('input[name=name]').val(),
    email: form.find('input[name=email]').val(),
    phone: form.find('input[name=phone]').val(),
    status: form.find('input[name=status]:checked').val(),
    role: form.find('input[name=role]:checked').val(),
    note: form.find('textarea[name=note]').val(),
    access_full: form.find('input[name=access_full]').is(':checked') ? 1 : 0,
    access_restaurants: form.find('select[name=access_restaurants]').val(),
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

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=email]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has email: <b class='text-dark'>" + form.find('input[name=email]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

    })
    .then(() => {

      form_loading(form, false);

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}
function user_full_restaurants(ele) {
  var bind = $(ele);
  var form = bind.closest('form');
  var role = form.find('input[name=role]:checked').val();

  if (role == 'admin') {
    form.find('input[name=access_full]').prop('checked', true);
    form.find('.access-restaurants').addClass('d-none');
    return false;
  }

  if (bind.is(':checked')) {
    form.find('.access-restaurants').addClass('d-none');
  } else {
    form.find('.access-restaurants').removeClass('d-none');
  }
}
function user_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));
  form.find('input[name=email]').val(tr.attr('data-email'));
  form.find('input[name=phone]').val(tr.attr('data-phone'));
  form.find('input[name=status][value=' + tr.attr('data-status') + ']').prop('checked', true);
  form.find('input[name=role][value=' + tr.attr('data-role') + ']').prop('checked', true);
  form.find('textarea[name=note]').val(tr.attr('data-note'));

  var access_full = parseInt(tr.attr('data-access-full'));
  if (access_full) {
    form.find('input[name=access_full]').prop('checked', true);
    form.find('.access-restaurants').addClass('d-none');
  } else {
    form.find('input[name=access_full]').prop('checked', false);
    form.find('.access-restaurants').removeClass('d-none');

    var datad = tr.attr('data-access-ids');
    if (datad && datad !== '') {
      datad = JSON.parse(datad);
      form.find('.access-restaurants select').selectize()[0].selectize.setValue(datad);
    }
  }

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function user_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/user/update', {
    item: form.find('input[name=item]').val(),
    name: form.find('input[name=name]').val(),
    email: form.find('input[name=email]').val(),
    phone: form.find('input[name=phone]').val(),
    status: form.find('input[name=status]:checked').val(),
    role: form.find('input[name=role]:checked').val(),
    note: form.find('textarea[name=note]').val(),
    access_full: form.find('input[name=access_full]').is(':checked') ? 1 : 0,
    access_restaurants: form.find('select[name=access_restaurants]').val(),
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

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=email]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has email: <b class='text-dark'>" + form.find('input[name=email]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

    })
    .then(() => {

      form_loading(form, false);
      form_close(form);
    });

  return false;
}
function user_delete_confirm(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_item');

  popup.find('input[name=item]').val(tr.attr('data-id'));
}
function user_delete(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/user/delete', {
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
function user_restore(ele) {
  var popup = $(ele).closest('.modal');

  axios.post('/admin/user/restore', {
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

    });

  return false;
}
function user_role(ele) {
  var form = $(ele).closest('form');
  var role = form.find('input[name=role]:checked').val();
  if (role == 'admin') {
    if (!form.find('input[name=access_full]').is(':checked')) {
      form.find('input[name=access_full]').prop('checked', true);
      form.find('.access-restaurants').addClass('d-none');
    }
  }
}
function user_profile_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_profile');
  popup.modal('show');
  return false;
}
function user_profile() {
  var form = $('#frm-profile');
  var popup = $('#modal_confirm_profile');
  form_loading(popup);

  axios.post('/admin/profile/update', {
    name: form.find('input[name=info_name]').val(),
    email: form.find('input[name=info_email]').val(),
    phone: form.find('input[name=info_phone]').val(),
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

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function user_code_confirm() {
  var popup = $('#modal_confirm_code');
  popup.modal('show');
  return false;
}
function user_code() {
  var popup = $('#modal_confirm_code');
  form_loading(popup);

  axios.post('/admin/profile/pwd/code', {})
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, 'Your verify code has been sent successfully!', true);

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
function user_pwd_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_pwd');
  popup.modal('show');
  return false;
}
function user_pwd() {
  var form = $('#frm-pwd');
  var popup = $('#modal_confirm_pwd');
  form_loading(popup);

  axios.post('/admin/profile/pwd/update', {
    code: form.find('input[name=pwd_code]').val(),
    password: form.find('input[name=pwd_pwd1]').val(),
    password_confirmation: form.find('input[name=pwd_pwd2]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);
      page_reload();

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
function user_setting_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_setting');
  popup.modal('show');
  return false;
}
function user_setting() {
  var form = $('#frm-setting');

  axios.post('/admin/profile/setting/update', {
    settings: {}
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
function user_test_sound() {
  sound_play();
}
function user_test_speaker() {
  speaker_tester();
}
function user_test_printer() {
  page_open(acmcfs.link_base_url + '/printer/test');
}
function user_setting_notify_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_setting_notify');
  popup.modal('show');
  return false;
}
function user_setting_notify() {
  var form = $('#frm-setting-notify');
  var popup = $('#modal_confirm_setting_notify');
  form_loading(popup);

  var notifications = [];

  form.find('.notify_item').each(function (k, v) {
    var bind = $(v);
    var notify = bind.attr('data-notify');
    var key = '';
    var val = '';

    key = notify + '_receive';
    val = bind.find('input[name=' + key + ']').is(':checked') ? 1 : 0;
    notifications.push({
      key: key,
      val: val,
    });

    key = notify + '_alert_printer';
    val = bind.find('input[name=' + key + ']').is(':checked') ? 1 : 0;
    notifications.push({
      key: key,
      val: val,
    });

    key = notify + '_alert_email';
    val = bind.find('input[name=' + key + ']').is(':checked') ? 1 : 0;
    notifications.push({
      key: key,
      val: val,
    });

    key = notify + '_alert_speaker';
    val = bind.find('input[name=' + key + ']').is(':checked') ? 1 : 0;
    notifications.push({
      key: key,
      val: val,
    });
  });

  axios.post('/admin/profile/setting/notify', {
    notifications: notifications
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

    })
    .then(() => {

      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}


