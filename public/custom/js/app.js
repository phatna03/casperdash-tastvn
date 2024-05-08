// custome
function bind_datad(wrap) {
  bind_number(wrap);
  bind_selectize(wrap);
}

function bind_selectize(wrap) {
  var wrapper = $(wrap);
  if (!wrapper.length) {
    wrapper = $('body');
  }

  if (wrapper && wrapper.find('select.ajx_selectize').length) {
    wrapper.find('select.ajx_selectize').each(function (k, v) {
      var select = $(v);
      var value = select.attr('data-value');
      var chosen = select.attr('data-chosen');

      if (value === 'ingredient') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/ingredient/selectize',
              type: 'post',
              data: {
                keyword: query,
                _token: acmcfs.var_csrf,
              },
              complete: function (xhr, textStatus) {
                var rsp = xhr.responseJSON;

                if (xhr.status == 200) {
                  select.options = rsp.items;
                  callback(rsp.items);

                  if (chosen && parseInt(chosen)) {
                    setTimeout(function () {
                      select.selectize()[0].selectize.setValue(chosen);
                    }, acmcfs.timeout_quick);
                  }
                }
              },
            });
          },
          create: function (input, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/ingredient/create',
              type: 'POST',
              data: {
                name: input,
                _token: acmcfs.var_csrf,
              },
              success: function (rsp) {
                select.options = rsp.items;
                callback(rsp.items);
              }
            });
          },
        });

        select.removeClass('ajx_selectize');

      } else if (value === 'food') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/food/selectize',
              type: 'post',
              data: {
                keyword: query,
                _token: acmcfs.var_csrf,
              },
              complete: function (xhr, textStatus) {
                var rsp = xhr.responseJSON;

                if (xhr.status == 200) {
                  select.options = rsp.items;
                  callback(rsp.items);

                  if (chosen && parseInt(chosen)) {
                    setTimeout(function () {
                      select.selectize()[0].selectize.setValue(chosen);
                    }, acmcfs.timeout_quick);
                  }
                }
              },
            });
          },
        });

        select.removeClass('ajx_selectize');

      } else if (value === 'restaurant') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/restaurant/selectize',
              type: 'post',
              data: {
                keyword: query,
                _token: acmcfs.var_csrf,
              },
              complete: function (xhr, textStatus) {
                var rsp = xhr.responseJSON;

                if (xhr.status == 200) {
                  select.options = rsp.items;
                  callback(rsp.items);

                  if (chosen && parseInt(chosen)) {
                    setTimeout(function () {
                      select.selectize()[0].selectize.setValue(chosen);
                    }, acmcfs.timeout_quick);
                  }
                }
              },
            });
          },
        });

        select.removeClass('ajx_selectize');

      } else if (value === 'restaurant_parent') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/restaurant/selectize/parent',
              type: 'post',
              data: {
                keyword: query,
                _token: acmcfs.var_csrf,
              },
              complete: function (xhr, textStatus) {
                var rsp = xhr.responseJSON;

                if (xhr.status == 200) {
                  select.options = rsp.items;
                  callback(rsp.items);

                  if (chosen && parseInt(chosen)) {
                    setTimeout(function () {
                      select.selectize()[0].selectize.setValue(chosen);
                    }, acmcfs.timeout_quick);
                  }
                }
              },
            });
          },
        });

        select.removeClass('ajx_selectize');

      } else if (value === 'user') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/user/selectize',
              type: 'post',
              data: {
                keyword: query,
                _token: acmcfs.var_csrf,
              },
              complete: function (xhr, textStatus) {
                var rsp = xhr.responseJSON;

                if (xhr.status == 200) {
                  select.options = rsp.items;
                  callback(rsp.items);

                  if (chosen && parseInt(chosen)) {
                    setTimeout(function () {
                      select.selectize()[0].selectize.setValue(chosen);
                    }, acmcfs.timeout_quick);
                  }
                }
              },
            });
          },
        });

        select.removeClass('ajx_selectize');

      }
    });
  }
  if (wrapper && wrapper.find('select.opt_selectize').length) {
    wrapper.find('select.opt_selectize').each(function (k, v) {
      var select = $(v);

      select.selectize({});
    });
  }
}

function bind_number(wrap) {
  var wrapper = $(wrap);
  //0= 48 //9= 57 //, = 44 //- = 45 //. = 46
  if (!wrapper.length) {
    wrapper = $('body');
  }

  wrapper.find('.fnumber').sys_format_number();
  wrapper.find('.fnumber').bind('keypress keyup blur', function (event) {
    $(this).val($(this).val().replace(/[^0-9\,]/g, '')); //positive
    if (!(event.which >= 48 && event.which <= 57)) {
      event.preventDefault();
    }

    // console.log(event.target);
    setTimeout(function () {
      if (event && event.type === 'blur') {
        var val = $(event.target).val();
        val = input_number_only(val);

        if (val && val > 0) {
          $(event.target).val(val);
          $(event.target).sys_format_number();
        } else {
          $(event.target).val('');
        }
      }

    }, acmcfs.timeout_quick);
  });
  wrapper.find('.fnumber').bind('paste', function (event) {
    $(this).val($(this).val().replace(/[^0-9\,]/g, '')); //positive

    // console.log(event.target);
    setTimeout(function () {
      if (event && event.type === 'blur') {
        var val = $(event.target).val();
        val = input_number_only(val);

        if (val && val > 0) {
          $(event.target).val(val);
          $(event.target).sys_format_number();
        } else {
          $(event.target).val('');
        }
      }

    }, acmcfs.timeout_quick);
  });
}

function bind_nl2br(str, is_xhtml) {
  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function bind_picker() {
  //date range
  if ($('.date_picker').length) {
    $('.date_picker').daterangepicker({
      timePicker: false,
      locale: {
        format: 'DD/MM/YYYY',
      },
    });
    $('.date_picker').val('');
  }

  //time range
  if ($('.date_time_picker').length) {

    $('.date_time_picker').each(function (k, v) {
      var bind = $(v);
      var value = bind.attr('data-value');

      if (value && value == 'current_month') {

        bind.daterangepicker({
          timePicker: true,
          timePickerIncrement: 30,
          locale: {
            format: 'DD/MM/YYYY HH:mm',
          },
          timePicker24Hour: true,
          startDate: moment().startOf('month').format('DD/MM/YYYY HH:mm'),
          // startDate: moment().subtract(1, 'months').startOf('month').format('DD/MM/YYYY HH:mm'),
          endDate: moment().endOf('month').format('DD/MM/YYYY HH:mm'),
        });

      } else if (value && value == 'current_day') {

        bind.daterangepicker({
          timePicker: true,
          timePickerIncrement: 30,
          locale: {
            format: 'DD/MM/YYYY HH:mm',
          },
          timePicker24Hour: true,
          startDate: moment().startOf('day').format('DD/MM/YYYY HH:mm'),
          endDate: moment().endOf('day').format('DD/MM/YYYY HH:mm'),
        });

      } else if (value && value == 'last_and_current_day') {

        bind.daterangepicker({
          timePicker: true,
          timePickerIncrement: 30,
          locale: {
            format: 'DD/MM/YYYY HH:mm',
          },
          timePicker24Hour: true,
          startDate: moment().subtract(1, 'days').startOf('day').format('DD/MM/YYYY HH:mm'),
          endDate: moment().endOf('day').format('DD/MM/YYYY HH:mm'),
        });

      } else {

        bind.daterangepicker({
          timePicker: true,
          timePickerIncrement: 30,
          locale: {
            format: 'DD/MM/YYYY HH:mm',
          },
          timePicker24Hour: true,
        });

        bind.val('');
      }

    });
  }
}

function bind_staff(role) {
  $('.no_' + role).closest('.menu-item').remove();
  $('.no_' + role).remove();
}

function input_number_only(value) {
  if (!value || value === '') {
    return 0;
  }

  value = value.toString();

  if (value && value !== '') {
    value = value.replace(/\./g, '');
  }
  if (value && value !== '') {
    value = value.replace(/,/g, '');
  }
  if (value && value !== '') {
    value = parseInt(value);
  }

  return !value || value === '' ? 0 : value;
}

function input_number_min_one(ele) {
  var bind = $(ele);
  var val = bind.val().trim();

  if (!val || val === '' || parseInt(val) <= 0) {
    bind.val(1);
  }
}

function offcanvas_close() {
  $('.offcanvas').removeClass('show');
  $('.offcanvas-backdrop').remove();
  $('body').attr('style', '');
}

function sound_play() {
  var audio = new Audio(acmcfs.link_base_url + '/sound_notification.mp3');
  audio.play();
}

function speaker_allow() {
  var audio = new Audio(acmcfs.link_speaker);
  audio.play();
}

function speaker_play() {
  var audio = new Audio(acmcfs.link_speaker_notify);
  audio.play();
}

function speaker_tester() {
  var audio = new Audio(acmcfs.link_speaker_tester);
  audio.play();
}

function message_from_toast(type, title, body, sound = false) {

  toastr.options = {
    autoDismiss: false,
    newestOnTop: true,
    positionClass: 'toast-bottom-left',
    onclick: null,
    rtl: isRtl
  };

  var htmlTitle = '<span class="badge bg-primary">' + title + '</span>';
  if (type == 'success') {
    htmlTitle = '<span class="text-success">' + title + '</span>';
  } else if (type == 'error') {
    htmlTitle = '<span class="badge bg-danger">' + title + '</span>';
  }

  toastr[type](body, htmlTitle);

  if (sound) {
    sound_play();
  }
}

function page_url(href, time_out = 0) {
  if (time_out && parseInt(time_out) > 0) {
    setTimeout(function () {
      parent.window.location.href = href;
    }, time_out);
  } else {
    parent.window.location.href = href;
  }
}

function page_loading(status = true) {
  if (status) {
    $("#preloader").removeClass('d-none');
  } else {
    $("#preloader").addClass('d-none');
  }
}

function page_reload(time_out = 0) {
  if (time_out && parseInt(time_out) > 0) {
    setTimeout(function () {
      window.location.reload(true);
    }, time_out);
  } else {
    window.location.reload(true);
  }
}

function page_open(href, time_out = 0) {
  if (time_out && parseInt(time_out) > 0) {
    setTimeout(function () {
      window.open(href, '_blank');
    }, time_out);
  } else {
    window.open(href, '_blank');
  }
}

function js_item_row_remove(ele) {
  var bind = $(ele);
  var row = bind.closest('.js-item-row');

  row.remove();
}

function datatable_refresh() {
  if (typeof datatable_listing !== "undefined") {
    datatable_listing.ajax.reload();
  }
}

function form_loading(frm, loading = true) {
  var form = $(frm);

  if (loading) {
    form.find('.wrap-btns .btn-loading').removeClass('d-none');
    form.find('.wrap-btns .btn-ok').addClass('d-none');
  } else {
    form.find('.wrap-btns .btn-loading').addClass('d-none');
    form.find('.wrap-btns .btn-ok').removeClass('d-none');
  }
}

function excel_check(sender) {
  var popup = jQuery('#modal_import');
  var validExts = new Array(".xlsx", ".xls");
  var fileExt = sender.value;

  fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
  if (validExts.indexOf(fileExt) < 0) {
    popup.find('input[name=file]').val('');

    message_from_toast('error', acmcfs.message_title_error, "Invalid excel file");
    return false;
  }

  return true;
};

// tastevn
function auth_form_active(frm_id) {
  $('.wrap_form_panel').addClass('d-none');
  $('#' + frm_id).removeClass('d-none');
}

function auth_form_login(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  form_loading(frm);

  axios.post('/auth/login', {
    email: form.find('input[name=email]').val(),
    password: form.find('input[name=pwd]').val(),
  })
    .then(response => {
      console.log('===THEN===');
      console.log(response.data);

      message_from_toast('success', acmcfs.message_title_success,
        '<span>Hi <b class="text-primary">' + response.data.user.name + '</b>, nice to see you!</span>', true);

      var url_redirect = acmcfs.link_base_url;
      if (response.data.redirect && response.data.redirect !== '') {
        url_redirect = response.data.redirect;
      }
      page_url(url_redirect, acmcfs.timeout_default);

    })
    .catch(error => {
      console.log('===ERROR===');
      console.log(error);
      console.log(error.response.data);
      console.log(Object.values(error.response.data));

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', 'Invalid Credentials', v);
        });
      }

      form_loading(frm, false);
    });

  return false;
}

function auth_form_forgot(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  form_loading(frm);

  axios.post('/auth/send-code', {
    email: form.find('input[name=email]').val(),
    code: form.find('input[name=code]').val(),
    step: form.find('input[name=step]').val(),
  })
    .then(response => {
      console.log('===THEN===');
      console.log(response.data);

      if (form.find('input[name=step]').val() == 'email') {

        message_from_toast('success', acmcfs.message_title_success, 'Your verify code has been sent successfully!', true);

        form.find('input[name=email]').prop('disabled', true);
        form.find('#wrap-forgot-code').removeClass('d-none');
        form.find('input[name=step]').val('code');
        form.find('button').text('Submit');

      } else if (form.find('input[name=step]').val() == 'code') {

        auth_form_active('formReset');
      }

      form_loading(frm, false);
    })
    .catch(error => {
      console.log('===ERROR===');
      console.log(error);
      console.log(error.response.data);
      console.log(Object.values(error.response.data));

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', 'Invalid Credentials', v);
        });
      }

      form_loading(frm, false);
    });

  return false;
}

function auth_form_reset(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var pwd1 = form.find('input[name=pwd1]').val();
  var pwd2 = form.find('input[name=pwd2]').val();

  var formForgot = $('#formForgot');

  form_loading(frm);

  axios.post('/auth/update-pwd', {
    email: formForgot.find('input[name=email]').val(),
    password: pwd1,
    password_confirmation: pwd2,
  })
    .then(response => {
      console.log('===THEN===');
      console.log(response.data);

      message_from_toast('success', acmcfs.message_title_success, 'Your changes have been updated successfully!', true);
      auth_form_active('formLogin');

      form_loading(frm, false);
    })
    .catch(error => {
      console.log('===ERROR===');
      console.log(error);
      console.log(error.response.data);
      console.log(Object.values(error.response.data));

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', 'Invalid Credentials', v);
        });
      }

      form_loading(frm, false);
    });

  return false;
}

function auth_logout() {

  axios.post('/auth/logout', {})
    .then(response => {
      console.log('===THEN===');
      console.log(response.data);

      message_from_toast('success', acmcfs.message_title_success,
        '<span>Goodbye, see you again!</span>', true);
      page_url(acmcfs.link_base_url + '/login', acmcfs.timeout_default);

    })
    .catch(error => {
      console.log('===ERROR===');
      console.log(error);
      console.log(error.response.data);
      console.log(Object.values(error.response.data));

      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

    });

  return false;
}

function sys_setting_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_item');
  popup.modal('show');
  return false;
}

function sys_setting() {
  var form = $('#frm-settings');

  axios.post('/admin/setting/update', {
    s3_region: form.find('input[name=s3_region]').val(),
    s3_api_key: form.find('input[name=s3_api_key]').val(),
    s3_api_secret: form.find('input[name=s3_api_secret]').val(),
    rbf_api_key: form.find('input[name=rbf_api_key]').val(),
    rbf_dataset_scan: form.find('input[name=rbf_dataset_scan]').val(),
    rbf_dataset_upload: form.find('input[name=rbf_dataset_upload]').val(),
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

    });

  return false;
}

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
      setTimeout(function () {
        user_clear(frm);
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
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

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
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

    });

  return false;
}

function user_code_confirm() {
  var popup = $('#modal_confirm_code');
  popup.modal('show');
  return false;
}

function user_code() {
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
  page_open(acmcfs.link_base_url + '/printer-test');
}

function user_setting_notify_confirm(evt, frm) {
  evt.preventDefault();
  var popup = $('#modal_confirm_setting_notify');
  popup.modal('show');
  return false;
}

function user_setting_notify() {
  var form = $('#frm-setting-notify');
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

    });

  return false;
}

function text_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  axios.post('/admin/text/store', {
    name: form.find('input[name=name]').val(),
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
          .append("Are you sure you want to restore item has text: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

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

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function restaurant_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  axios.post('/admin/restaurant/store', {
    name: form.find('input[name=name]').val(),
    s3_bucket_name: form.find('input[name=s3_bucket_name]').val(),
    s3_bucket_address: form.find('input[name=s3_bucket_address]').val(),
    rbf_scan: form.find('input[name=rbf_scan]').is(':checked') ? 1 : 0,
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
          if (v == 'can_restored') {
            restoreModal = true;
          } else {
            message_from_toast('error', acmcfs.message_title_error, v);
          }
        });
      }

      if (restoreModal && $('#modal_restore_item').length) {
        $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

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
  form.find('input[name=s3_bucket_name]').val(tr.attr('data-s3_bucket_name'));
  form.find('input[name=s3_bucket_address]').val(tr.attr('data-s3_bucket_address'));

  form.find('input[name=rbf_scan]').prop('checked', false);
  if (parseInt(tr.attr('data-rbf_scan'))) {
    form.find('input[name=rbf_scan]').prop('checked', true);
  }

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}

function restaurant_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  axios.post('/admin/restaurant/update', {
    item: form.find('input[name=item]').val(),
    name: form.find('input[name=name]').val(),
    s3_bucket_name: form.find('input[name=s3_bucket_name]').val(),
    s3_bucket_address: form.find('input[name=s3_bucket_address]').val(),
    rbf_scan: form.find('input[name=rbf_scan]').is(':checked') ? 1 : 0,
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
        $('#modal_restore_item input[name=item]').val(form.find('input[name=name]').val());
        $('#modal_restore_item .alert').empty()
          .append("Are you sure you want to restore item has name: <b class='text-dark'>" + form.find('input[name=name]').val() + "</b>");
        $('#modal_restore_item').modal('show');
      }

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function restaurant_delete_confirm(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_item');

  popup.find('input[name=item]').val(tr.attr('data-id'));
}

function restaurant_delete(ele) {
  var popup = $(ele).closest('.modal');

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

    });

  return false;
}

function restaurant_restore(ele) {
  var popup = $(ele).closest('.modal');

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

    });

  return false;
}

function restaurant_info(id) {
  page_url(acmcfs.link_base_url + '/admin/restaurant/info/' + id);
}

function restaurant_import_foods(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  const formData = new FormData();
  formData.append('excel', form.find('input[type=file]')[0].files[0]);
  formData.append('restaurant_id', form.find('input[name=restaurant_id]').val());

  axios.post('/admin/restaurant/food/import', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      if (typeof datatable_listing_food_refresh !== "undefined") {
        datatable_listing_food_refresh();
      }

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

function restaurant_add_foods(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  axios.post('/admin/restaurant/food/add', {
    item: form.find('input[name=item]').val(),
    category: form.find('select[name=category]').val(),
    foods: form.find('select[name=foods]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      if (typeof datatable_listing_food_refresh !== "undefined") {
        datatable_listing_food_refresh();
      }

      offcanvas_close();

      setTimeout(function () {
        form.find('select[name=category]').selectize()[0].selectize.setValue('');
        form.find('select[name=foods]').selectize()[0].selectize.setValue('[]');
      }, acmcfs.timeout_quick);

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

function restaurant_delete_food_confirm(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_food_out_restaurant');

  popup.find('input[name=food_id]').val(tr.attr('data-food_id'));
}

function restaurant_delete_food(ele) {
  var popup = $(ele).closest('.modal');

  axios.post('/admin/restaurant/food/delete', {
    item: popup.find('input[name=restaurant_id]').val(),
    food: popup.find('input[name=food_id]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      if (typeof datatable_listing_food_refresh !== "undefined") {
        datatable_listing_food_refresh();
      }

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

function restaurant_get_scan_results(ele, id) {
  var parent = $(ele).closest('.dt-buttons');

  $(ele).find('.wrap-btn').removeClass('bg-primary').addClass('bg-danger');
  $(ele).find('.wrap-btn span').text('Loading...');

  axios.post('/admin/restaurant/food/scan', {
    item: id,
    date: parent.find('input[name=get_date]').val(),
    hour: parent.find('input[name=get_hour]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      $(ele).find('.wrap-btn').addClass('bg-primary').removeClass('bg-danger');
      $(ele).find('.wrap-btn span').text('Submit');

      $('body').click();

      if (typeof datatable_listing_scan_refresh !== "undefined") {
        datatable_listing_scan_refresh();
      }

      if (response.data.notify && parseInt(response.data.notify)) {
        var htmlToast = '<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + response.data.notify + ')">Dish found error</div>';
        message_from_toast('error', 'Notification', htmlToast);
      }
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

function restaurant_food_scan_result_info(id) {
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

  axios.post('/admin/restaurant/food/scan/info', {
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

function restaurant_food_scan_result_info_action(next = 0) {
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
    restaurant_food_scan_result_info_rebind(view_next);
  }
}

function restaurant_food_scan_result_info_rebind(id) {
  var popup = $('#modal_food_scan_info');

  popup.find('.modal-body').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/restaurant/food/scan/info', {
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

function restaurant_food_scan_result_select(ele) {
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

function restaurant_food_scan_result_update_confirm() {
  var popup = $('#modal_food_scan_info_update');
  var view_current = parseInt($('body input[name=popup_view_id_itm]').val());

  axios.post('/admin/restaurant/food/scan/get', {
    item: view_current,
  })
    .then(response => {

      popup.find('.modal-body').empty()
        .append(response.data.html_info);

      bind_datad(popup);
      popup.modal('show');

      setTimeout(function () {
        popup.find('#user-update-food select').attr('onchange', 'restaurant_food_scan_result_select(this)');
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

function restaurant_food_scan_result_update(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var view_current = parseInt($('body input[name=popup_view_id_itm]').val());

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

  axios.post('/admin/restaurant/food/scan/update', {
    item: view_current,
    note: form.find('textarea[name=update_note]').val(),
    food: form.find('select[name=update_food]').val(),
    missings: missings,
    texts: texts,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);
      restaurant_food_scan_result_info_rebind(view_current);

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

function restaurant_food_scan_error_info(ele) {
  var tr = $(ele);
  var popup = $('#modal_food_scan_error');

  popup.find('.modal-header h4').text('Loading...');
  popup.find('.modal-body').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/restaurant/food/scan/error', {
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

function restaurant_food_scan_view(id) {
  axios.post('/admin/photo/view', {
    item: id,
  })
    .then(response => {
      //
    })
    .catch(error => {
      //
    });

  return false;
}

function restaurant_food_scan_cmt(ele) {
  var parent = $(ele).closest('#lcl_wrap');
  var content = parent.find('textarea[name=note]').val();
  var object_id = parent.find('input[name=object_id]').val();

  axios.post('/admin/comment/note', {
    object_id: object_id,
    object_type: 'restaurant_food_scan',
    content: content,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      if ($('body .restaurant_food_scan_' + object_id).length) {
        $('body .restaurant_food_scan_' + object_id).attr('data-lcl-txt', content);
      }

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

function restaurant_search_food_scan(ele) {
  var form = $(ele).closest('form');
  if (form.length) {
    setTimeout(function () {
      if (typeof datatable_listing_scan_refresh !== "undefined") {
        datatable_listing_scan_refresh();
      }
    }, acmcfs.timeout_default);
  }
}

function restaurant_search_food_scan_error(ele) {
  var form = $(ele).closest('form');
  if (form.length) {
    setTimeout(function () {
      if (typeof datatable_listing_error_refresh !== "undefined") {
        datatable_listing_error_refresh();
      }
    }, acmcfs.timeout_default);
  }
}

function restaurant_delete_food_scan_confirm(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_food_scan');

  popup.find('input[name=itd]').val(tr.attr('data-itd'));
}

function restaurant_delete_food_scan(ele) {
  var popup = $(ele).closest('.modal');

  axios.post('/admin/restaurant/food/scan/delete', {
    item: popup.find('input[name=itd]').val(),
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

    });

  return false;
}

function food_category_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  axios.post('/admin/food-category/store', {
    name: form.find('input[name=name]').val(),
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

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function food_category_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}

function food_category_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  axios.post('/admin/food-category/update', {
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

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function ingredient_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

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

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function food_clear(frm) {
  var form = $(frm);

  form.find('input[name=name]').val('');
  form.find('select[name=live_group]').selectize()[0].selectize.setValue(3);
}

function food_item(frm) {
  var form = $(frm);
  var ingredients = [];

  if (form.find('.wrap-add-item-ingredients .wrap-fetch .food-ingredient-item').length) {
    form.find('.wrap-add-item-ingredients .wrap-fetch .food-ingredient-item').each(function (k, v) {
      var tr = $(v);
      var ing_name = parseInt(tr.find('select[name=ing_name]').val());
      if (ing_name && ing_name > 0) {
        ingredients.push({
          id: ing_name,
          quantity: input_number_only(tr.find('input[name=ing_quantity]').val())
            ? input_number_only(tr.find('input[name=ing_quantity]').val()) : 1,

          color: tr.find('input[name=ing_color]').length ? tr.find('input[name=ing_color]').val() : '',
          core: tr.find('input[name=ing_core]').length && tr.find('input[name=ing_core]').is(':checked') ? 1 : 0,

          old: tr.find('input[name=old]').length ? tr.find('input[name=old]').val() : 0,
        });
      }
    });
  }

  return ingredients;
}

function food_add_clear() {
  var form = $('#offcanvas_add_item form');

  form.find('input[name=name]').val('');
  form.find('select[name=live_group]').selectize()[0].selectize.setValue(3);

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}

function food_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  // var ingredients = food_item(frm);
  // if (!ingredients.length) {
  //   message_from_toast('error', acmcfs.message_title_error, "Ingredients required", true);
  //   return false;
  // }

  const formData = new FormData();
  formData.append("name", form.find('input[name=name]').val());
  formData.append("live_group", form.find('select[name=live_group]').val());
  // formData.append("ingredients", JSON.stringify(ingredients));

  // Read selected files
  // if (form.find('input[type=file]')[0].files.length) {
  //   formData.append('photo[]', form.find('input[type=file]')[0].files[0]);
  // }

  axios.post('/admin/food/store', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();
      setTimeout(function () {
        food_clear(frm);
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function ingredient_item_focus(ele, focus = 0) {
  var parent = $(ele).closest('.food-ingredient-item');
  parent.removeClass('acm-border-focus');
  if (parseInt(focus)) {
    parent.addClass('acm-border-focus');
  }
}

function ingredient_item_add(ele) {
  var form = $(ele).closest('form');

  axios.post('/admin/food/ingredient/html', {})
    .then(response => {

      form.find('.wrap-add-item-ingredients .wrap-fetch').append(response.data.html);
      bind_datad(form);

    })
    .catch(error => {

    });

  return false;
}

function ingredient_item_remove(ele) {
  var parent = $(ele).closest('.food-ingredient-item');
  parent.remove();
}

function recipe_item_add(ele) {
  var form = $(ele).closest('form');

  axios.post('/admin/food/recipe/html', {})
    .then(response => {

      form.find('.wrap-add-item-ingredients .wrap-fetch').append(response.data.html);
      bind_datad(form);

    })
    .catch(error => {

    });

  return false;
}

function food_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=name]').val('');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));
  form.find('select[name=live_group]').selectize()[0].selectize.setValue(tr.attr('data-live_group'));

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}

function food_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  const formData = new FormData();
  formData.append("item", form.find('input[name=item]').val());
  formData.append("name", form.find('input[name=name]').val());
  formData.append("live_group", form.find('select[name=live_group]').val());

  axios.post('/admin/food/update', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();
      // offcanvas_close();

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}

function food_info(id) {
  var popup = $('#modal_food_info');

  popup.find('.modal-header h4').text('Loading...');

  popup.find('.modal-body .food_info_select select[name=restaurant_parent_id]').selectize()[0].selectize.setValue('');
  popup.find('.modal-body .food_info_img img').removeAttr('src').attr('src', acmcfs.link_food_no_photo);
  popup.find('.modal-body .food_info_ingredients').empty();
  popup.find('.modal-body input[name=item]').val(id);

  axios.post('/admin/food/get', {
    item: id,
  })
    .then(response => {

      popup.find('.modal-header h4').text(response.data.item.name);

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

function food_info_select(ele) {
  var bind = $(ele);
  var popup = bind.closest('.modal');

  var selected = bind.val();
  if (!selected || selected == '') {
    return false;
  }

  popup.find('.modal-body .food_info_img img').removeAttr('src').attr('src', acmcfs.link_food_no_photo);
  popup.find('.modal-body .food_info_ingredients').empty();

  axios.post('/admin/food/get/info', {
    item: popup.find('.modal-body input[name=item]').val(),
    restaurant_parent_id: selected,
  })
    .then(response => {

      if (response.data.food_photo && response.data.food_photo != '') {
        popup.find('.modal-body .food_info_img img').removeAttr('src').attr('src', response.data.food_photo);
      }

      if (response.data.html_ingredient && response.data.html_ingredient != '') {
        popup.find('.modal-body .food_roboflow').append(response.data.html_ingredient);
      }
      if (response.data.html_recipe && response.data.html_recipe != '') {
        popup.find('.modal-body .food_recipe').append(response.data.html_recipe);
      }

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

function food_import(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  const formData = new FormData();
  formData.append('excel', form.find('input[type=file]')[0].files[0]);
  formData.append('restaurant_parent_id', form.find('select[name=restaurant_parent_id]').val());

  axios.post('/admin/food/import', formData)
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
    });

  return false;
}

function food_import_recipe(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  const formData = new FormData();
  formData.append('excel', form.find('input[type=file]')[0].files[0]);
  formData.append('restaurant_parent_id', form.find('select[name=restaurant_parent_id]').val());

  axios.post('/admin/food/import/recipe', formData)
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
    });

  return false;
}

function food_edit_prepare_ingredient(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_ingredient form');

  form.find('.wrap-edit-ingredients').addClass('d-none');
  form.find('.wrap-add-item-ingredients .wrap-fetch').empty();
  form.find('select[name=restaurant_parent_id]').selectize()[0].selectize.setValue('');

  form.find('input[name=item]').val(tr.attr('data-id'));
}

function food_edit_select_ingredient(ele) {
  var bind = $(ele);
  var form = bind.closest('form');

  form.find('.wrap-edit-ingredients').addClass('d-none');
  form.find('.wrap-add-item-ingredients .wrap-fetch').empty();

  var selected = bind.val();
  if (!selected || selected == '') {
    return false;
  }

  axios.post('/admin/food/get/ingredient', {
    item: form.find('input[name=item]').val(),
    restaurant_parent_id: selected,
  })
    .then(response => {

      if (response.data.html && response.data.html != '') {
        form.find('.wrap-edit-ingredients').removeClass('d-none');
        form.find('.wrap-add-item-ingredients .wrap-fetch').append(response.data.html);
        bind_datad(form);
      }

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });
}

function food_edit_ingredient(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  var ingredients = food_item(frm);
  if (!ingredients.length) {
    message_from_toast('error', acmcfs.message_title_error, "Ingredients required", true);
    return false;
  }

  const formData = new FormData();
  formData.append("item", form.find('input[name=item]').val());
  formData.append("restaurant_parent_id", form.find('select[name=restaurant_parent_id]').val());
  formData.append("ingredients", JSON.stringify(ingredients));

  axios.post('/admin/food/update/ingredient', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();
      // offcanvas_close();

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

function food_edit_prepare_recipe(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_recipe form');

  form.find('.wrap-edit-ingredients').addClass('d-none');
  form.find('.wrap-add-item-ingredients .wrap-fetch').empty();
  form.find('select[name=restaurant_parent_id]').selectize()[0].selectize.setValue('');

  form.find('input[name=item]').val(tr.attr('data-id'));
}

function food_edit_select_recipe(ele) {
  var bind = $(ele);
  var form = bind.closest('form');

  form.find('.wrap-edit-ingredients').addClass('d-none');
  form.find('.wrap-add-item-ingredients .wrap-fetch').empty();

  var selected = bind.val();
  if (!selected || selected == '') {
    return false;
  }

  axios.post('/admin/food/get/recipe', {
    item: form.find('input[name=item]').val(),
    restaurant_parent_id: selected,
  })
    .then(response => {

      if (response.data.html && response.data.html != '') {
        form.find('.wrap-edit-ingredients').removeClass('d-none');
        form.find('.wrap-add-item-ingredients .wrap-fetch').append(response.data.html);
        bind_datad(form);
      }

    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    });
}

function food_edit_recipe(evt, frm) {
  evt.preventDefault();
  var form = $(frm);

  var ingredients = food_item(frm);
  if (!ingredients.length) {
    message_from_toast('error', acmcfs.message_title_error, "Ingredients required", true);
    return false;
  }

  const formData = new FormData();
  formData.append("item", form.find('input[name=item]').val());
  formData.append("restaurant_parent_id", form.find('select[name=restaurant_parent_id]').val());
  formData.append("ingredients", JSON.stringify(ingredients));

  axios.post('/admin/food/update/recipe', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();
      // offcanvas_close();

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

function notification_read(ele) {
  var bind = $(ele);

  if (bind.hasClass('bg-primary-subtle')) {
    bind.removeClass('bg-primary-subtle');

    axios.post('/admin/notification/read', {
      item: bind.attr('data-itd'),
    })
      .then(response => {

      })
      .catch(error => {

      });
  }

  return false;
}

function notification_read_all() {
  var wrap = $('#wrap-notifications');
  wrap.find('.acm-itm-notify').removeClass('bg-primary-subtle');

  axios.post('/admin/notification/read/all', {})
    .then(response => {

    })
    .catch(error => {

    });

  return false;
}

function notification_navbar() {
  var wrap = $('#navbar-notifications');

  wrap.find('.navbar-ul').empty()
    .append('<li class="list-group-item m-auto">' + acmcfs.html_loading + '</li>');

  axios.post('/admin/notification/latest', {})
    .then(response => {

      if (response.data.html) {
        wrap.find('.navbar-ul').empty()
          .append(response.data.html);
      } else {
        wrap.find('.navbar-ul').empty()
          .append('<li><div class="alert alert-warning">No notification found</div></li>');
      }

      bind_datad(wrap);

    })
    .catch(error => {

    });

  return false;
}

function notification_newest() {
  axios.post('/admin/notification/newest', {})
    .then(response => {

      if (response.data.items && response.data.items.length) {

        response.data.items.forEach(function (v, k) {

          var html_toast = '<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + v.itd + ')">';
          html_toast += '<div class="acm-fs-13">+ Predicted Dish: <b><span class="acm-mr-px-5 text-danger">' + v.food_confidence + '%</span><span>' + v.food_name + '</span></b></div>';

          html_toast += '<div class="acm-fs-13">+ Ingredients Missing:</div>';
          v.ingredients.forEach(function (v1, k1) {
            if (v1 && v1 !== '' && v1.trim() !== '') {
              html_toast += '<div class="acm-fs-13 acm-ml-px-10">- ' + v1 + '</div>';
            }
          });

          html_toast += '</div>';
          message_from_toast('info', v.restaurant_name, html_toast, true);
        });


        if (response.data.printer) {
          page_open(acmcfs.link_base_url + '/printer?ids=' + response.data.ids.toString());
        }
      }

      if (response.data.speaker) {
        setTimeout(function () {
          speaker_play();
        }, acmcfs.timeout_quick);
      }

      if (response.data.role) {
        bind_staff(response.data.role);
      }
    })
    .catch(error => {

    });

  return false;
}

function roboflow_retraining_confirm() {
  var popup = $('#modal_roboflow_retraining');
  popup.modal('show');
  return false;
}

function roboflow_retraining() {
  var tbl = $('#datatable-listing-scan');
  var ids = [];

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

  axios.post('/admin/roboflow/retrain', {
    items: ids,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);

      if (typeof datatable_listing_scan_refresh !== "undefined") {
        datatable_listing_scan_refresh();
      }

    })
    .catch(error => {

    });
}

function stats_clear(ele, type) {
  var wrap = $(ele).closest('.wrap-stats');

  wrap.find('input[name=search_time]').val('').trigger('change');
}

function stats_total() {
  var wrap = $('#wrap-stats-total');

  var times = wrap.find('input[name=search_time]').val();

  wrap.find('.wrap-search-condition').addClass('d-none');
  if (times && times !== '') {
    wrap.find('.wrap-search-condition').removeClass('d-none');
    wrap.find('.wrap-search-condition .search-time').empty().text(times);
  }

  axios.post('/admin/restaurant/stats', {
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

function stats_total_by_date(start_date, end_date) {
  // console.log(start_date, start_date);

  var wrap = $('#wrap-stats-total');
  wrap.find('input[name=search_time]').daterangepicker({
    timePicker: true,
    timePickerIncrement: 30,
    locale: {
      format: 'DD/MM/YYYY HH:mm',
    },
    startDate: start_date + ' 00:00',
    endDate: end_date + ' 00:00',
  });
}
