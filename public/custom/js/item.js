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
