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

  //multi_selectize
  var plugins = [];

  if (wrapper && wrapper.find('select.ajx_selectize').length) {
    wrapper.find('select.ajx_selectize').each(function (k, v) {
      var select = $(v);
      var value = select.attr('data-value');
      var chosen = select.attr('data-chosen');
      var restaurant_parent_id = select.attr('data-restaurant');

      plugins = [];
      if (select.hasClass('multi_selectize')) {
        plugins = ["remove_button"];
      }

      if (value === 'ingredient') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          plugins: plugins,
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

        var restaurant_parent_id = $('body input[name=current_restaurant_parent_id]').val();

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          plugins: plugins,
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/food/selectize',
              type: 'post',
              data: {
                keyword: query,
                restaurant_parent_id: restaurant_parent_id,
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
          plugins: plugins,
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/sensor/selectize',
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
          plugins: plugins,
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

      } else if (value === 'user') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          plugins: plugins,
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

      } else if (value === 'restaurant_food') {

        select.selectize({
          valueField: 'id',
          labelField: 'name',
          searchField: 'name',
          plugins: plugins,
          preload: true,
          clearCache: function (template) {
          },
          load: function (query, callback) {
            $.ajax({
              url: acmcfs.link_base_url + '/admin/restaurant/food/get',
              type: 'post',
              data: {
                keyword: query,
                restaurant_parent_id: restaurant_parent_id,
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

      if (select.hasClass('multi_selectize')) {
        plugins = ["remove_button"];
      }

      select.selectize({
        plugins: plugins,
      });
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
function input_number_min_two(ele) {
  var bind = $(ele);
  var val = bind.val().trim();

  if (!val || val === '' || parseInt(val) <= 2) {
    bind.val(2);
  }
}
function input_number_min_30_max_100(ele) {
  var bind = $(ele);
  var val = bind.val().trim();

  if (!val || val === '' || parseInt(val) <= 30) {
    bind.val(30);
  }
  if (!val || val === '' || parseInt(val) > 100) {
    bind.val(99);
  }
}
function input_date_time(date) {
  var d = new Date(date),
    month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    year = d.getFullYear(),
    hour = '' + d.getHours(),
    minute = '' + d.getMinutes(),
    second = '' + d.getSeconds();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;
  if (hour.length < 2) hour = '0' + hour;
  if (minute.length < 2) minute = '0' + minute;
  if (second.length < 2) second = '0' + second;

  return [day, month, year].join('/') + ' ' + [hour, minute, second].join(':');
}

function sound_play() {
  var audio = new Audio(acmcfs.link_base_url + '/sound_notification.mp3');
  if (acmcfs.dev_mode != 'local') {
    audio.play();
  }
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
function form_close(frm) {
  var form = $(frm);

  form.find('.wrap-btns .btn-cancel')[0].click();
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

function toggle_header() {
  $('#layout-navbar').toggleClass('d-none');
  $('#layout-menu').toggleClass('d-none');
  $('.page_main_footer').toggleClass('d-none');

  if ($('#layout-navbar').hasClass('d-none')) {
    $('.layout-page').addClass('hidden_header');
  } else {
    $('.layout-page').removeClass('hidden_header');
  }

  if ($('#layout-menu').hasClass('d-none')) {
    $('.page_main_content').addClass('hidden_header');
  } else {
    $('.page_main_content').removeClass('hidden_header');
  }
}

//auth
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

//notification
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
  if (acmcfs.notify_running) {
    return false;
  }

  acmcfs.notify_running = 1;

  axios.post('/admin/notification/newest', {})
    .then(response => {

      if (response.data.items && response.data.items.length) {

        response.data.items.forEach(function (v, k) {

          var html_toast = '<div class="cursor-pointer" onclick="sensor_food_scan_info(' + v.itd + ')">';
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


        //temp off
        // if (response.data.printer) {
        //   page_open(acmcfs.link_base_url + '/printer?ids=' + response.data.ids.toString());
        // }
      }

      if (response.data.speaker) {
        setTimeout(function () {
          speaker_play();
        }, acmcfs.timeout_quick);
      }

      if (response.data.role) {
        bind_staff(response.data.role);
      }

      acmcfs.notify_running = 0;
    })
    .catch(error => {

    });

  return false;
}

//roboflow
function roboflow_retraining_confirm() {
  var popup = $('#modal_roboflow_retraining');
  popup.modal('show');
  return false;
}

//internet
let download_time_start, download_time_end;

function internet_download_check(img_url) {
  download_time_start = (new Date()).getTime();
  var download = new Image();
  download.src = img_url + '?' + download_time_start;
  download.onload = function() {
    download_time_end = (new Date()).getTime();
    internet_download_speed();
  }
}

function internet_download_speed() {
  var duration = (download_time_end - download_time_start) / 1000;
  var bitsLoaded = 1024 * 1024 * 5 * 8;
  var bps = (bitsLoaded / duration).toFixed(2);
  var kbps = (bps / 1024).toFixed(2);
  var mbps = (kbps / 1024).toFixed(2);

  if ($('.result_time_check').length) {

    var html = '<div class="internet_speed position-relative">' +
      '<div class="mb-2 acm-clearfix">' +
      '<div class="text-dark fw-bold acm-float-right">' + mbps + '</div>' +
      '<div class="text-dark overflow-hidden">Speed Download (mbps): </div>' +
      '</div>' +
      '</div>';

    $('.result_time_check .data_result .internet_speed').remove();
    $('.result_time_check .data_result').append(html);
  }

  return mbps;

  // console.log(`Your connection speed:
  //   ${bps} bps
  //   ${kbps} kbps
  //   ${mbps} mbps`);
}

