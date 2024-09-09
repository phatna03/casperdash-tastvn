//restaurant
function restaurant_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/restaurant/store', {
    name: form.find('input[name=name]').val(),
    model_name: form.find('input[name=model_name]').val(),
    model_version: form.find('input[name=model_version]').val(),
    model_scan: form.find('input[name=model_scan]').is(':checked') ? 1 : 0,
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

  form.find('input[name=model_name]').val(tr.attr('data-model_name'));
  form.find('input[name=model_version]').val(tr.attr('data-model_version'));

  form.find('input[name=model_scan]').prop('checked', false);
  if (parseInt(tr.attr('data-model_scan'))) {
    form.find('input[name=model_scan]').prop('checked', true);
  }

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
    model_name: form.find('input[name=model_name]').val(),
    model_version: form.find('input[name=model_version]').val(),
    model_scan: form.find('input[name=model_scan]').is(':checked') ? 1 : 0,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

      form_close(form);
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

      form_close(form);
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

      var wrap_foods = popup1.find('.frm_restaurant_foods');

      wrap_foods.find('.count_foods').text('(' + response.data.count_foods + ')');
      wrap_foods.find('.count_foods_1').text(response.data.count_foods_1);
      wrap_foods.find('.count_foods_2').text(response.data.count_foods_2);
      wrap_foods.find('.count_foods_3').text(response.data.count_foods_3);

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
function restaurant_food_photo_prepare(ele) {
  var food_item = $(ele).closest('.data_food_item');
  var form = $('#frm_food_photo_standard');

  form.find('input[name=food_id]').val(food_item.attr('data-food_id'));

  var popup = form.closest('.modal');
  if (popup.length) {
    popup.find('input[name=restaurant_parent_id]').val(food_item.attr('data-restaurant_parent_id'));
  }

  form.find('input[type=file]').val("");
  form.find('input[type=file]')[0].click();
}
function restaurant_food_photo(ele) {
  var form = $('#frm_food_photo_standard');
  var food_id = form.find('input[name=food_id]').val();

  var popup = form.closest('.modal');
  var restaurant_parent_id = popup.find('input[name=restaurant_parent_id]').val();

  //photo
  var bind = $(ele);
  var url = bind.val();
  var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
  var img_exts = ['jpg', 'jpeg', 'png', 'webp'];

  if (ele.files[0].size < 0 || !img_exts.includes(ext)) {
    bind.val("");

    message_from_toast('error', acmcfs.message_title_error, 'Invalid photo', true);
    return false;
  }

  var reader = new FileReader();
  reader.onload = function(e) {
    $('#food_photo_standard_' + restaurant_parent_id + '_' + food_id).removeAttr('src')
      .attr('src', e.target.result);
  }
  reader.readAsDataURL(ele.files[0]);

  //check
  const formData = new FormData();
  formData.append("food_id", food_id);
  formData.append("restaurant_parent_id", restaurant_parent_id);

  // Read selected files
  if (form.find('input[type=file]')[0].files.length) {
    formData.append('photo[]', form.find('input[type=file]')[0].files[0]);
  }

  axios.post('/admin/restaurant/food/photo', formData)
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
function restaurant_food_update_prepare(ele, type) {
  var food_item = $(ele).closest('.data_food_item');
  var popup2 = $('#modal_food_update');

  popup2.find('input[name=restaurant_parent_id]').val(food_item.attr('data-restaurant_parent_id'));

  var popup1 = $(ele).closest('.modal');
  if (popup1.length) {
    popup2.find('input[name=restaurant_parent_id]').val(popup1.find('input[name=restaurant_parent_id]').val());
  }

  popup2.find('input[name=food_id]').val(food_item.attr('data-food_id'));
  popup2.find('input[name=type]').val(type);

  popup2.find('.wrap_updated').addClass('d-none');
  popup2.find('.wrap_' + type).removeClass('d-none');

  popup2.find('input[name=model_name]').val(food_item.attr('data-model_name'));
  popup2.find('input[name=model_version]').val(food_item.attr('data-model_version'));
  popup2.find('input[name=category_name]').val(food_item.attr('data-category_name'));
  popup2.find('input[name=confidence]').val(food_item.attr('data-confidence'));

  var live_group = parseInt(food_item.attr('data-live_group'));
  popup2.find('.wrap_live_group .live_group_' + live_group).prop('checked', true);

  popup2.modal('show');

  if (type == 'model_name') {
    setTimeout(function () {
      popup2.find('input[name=model_name]').focus();
    }, acmcfs.timeout_quick);
  } else if (type == 'model_version') {
    setTimeout(function () {
      popup2.find('input[name=model_version]').focus();
    }, acmcfs.timeout_quick);
  } else if (type == 'category_name') {
    setTimeout(function () {
      popup2.find('input[name=category_name]').focus();
    }, acmcfs.timeout_quick);
  } else if (type == 'confidence') {
    setTimeout(function () {
      popup2.find('input[name=confidence]').focus();
    }, acmcfs.timeout_quick);
  }
}
function restaurant_food_update() {
  var popup1 = $('#modal_info_item');
  var popup2 = $('#modal_food_update');
  form_loading(popup2);

  var restaurant_parent_id = popup2.find('input[name=restaurant_parent_id]').val();
  var food_id = popup2.find('input[name=food_id]').val();
  var type = popup2.find('input[name=type]').val();
  var live_group = popup2.find('input[name=live_group]:checked').val();
  var model_name = popup2.find('input[name=model_name]').val();
  var model_version = popup2.find('input[name=model_version]').val();
  var category_name = popup2.find('input[name=category_name]').val();
  var confidence = popup2.find('input[name=confidence]').val();

  axios.post('/admin/restaurant/food/update', {
    restaurant_parent_id: restaurant_parent_id,
    food_id: food_id,
    type: type,
    live_group: live_group,
    model_name: model_name,
    model_version: model_version,
    category_name: category_name,
    confidence: confidence,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      if (popup1.length) {

        var food_item = popup1.find('.data_food_item_' + food_id);
        switch (type) {
          case 'live_group':
            food_item.attr('data-live_group', live_group);

            var texted = 'Super Confidence';
            if (parseInt(live_group) == 2) {
              texted = 'Less Training';
            } else if (parseInt(live_group) == 3) {
              texted = 'Not Trained Yet';
            }
            food_item.find('.btn_inputs input[name=live_group]').val(texted);
            break;
          case 'model_name':
            food_item.attr('data-model_name', model_name);
            food_item.find('.btn_inputs input[name=model_name]').val(model_name);
            break;
          case 'model_version':
            food_item.attr('data-model_version', model_version);
            food_item.find('.btn_inputs input[name=model_version]').val(model_version);
            break;
          case 'confidence':
            food_item.attr('data-confidence', confidence);
            food_item.find('.btn_inputs input[name=confidence]').val(confidence);
            break;
          case 'category_name':
            food_item.attr('data-category_name', category_name);
            food_item.find('.food_category_name').text('(' + category_name + ')');
            break;
        }

        var wrap_foods = popup1.find('.frm_restaurant_foods');

        wrap_foods.find('.count_foods').text('(' + response.data.count_foods + ')');
        wrap_foods.find('.count_foods_1').text(response.data.count_foods_1);
        wrap_foods.find('.count_foods_2').text(response.data.count_foods_2);
        wrap_foods.find('.count_foods_3').text(response.data.count_foods_3);

      }
      else if ($('.tr_restaurant_food_' + restaurant_parent_id + '_' + food_id).length) {
        restaurant_food_serve_tr(restaurant_parent_id, food_id, response.data.datas);
      }
    })
    .catch(error => {
      if (error.response.data && Object.values(error.response.data).length) {
        Object.values(error.response.data).forEach(function (v, k) {
          message_from_toast('error', acmcfs.message_title_error, v);
        });
      }
    })
    .then(res => {
      form_loading(popup2, false);
      form_close(popup2);
    });

  return false;
}
function restaurant_food_add_prepare(itd) {
  var popup = $('#modal_food_add');

  popup.find('input[name=restaurant_parent_id]').val(itd);
  popup.find('input[name=name]').val('');
  popup.find('input[name=category_name]').val('');

  popup.modal('show');

  setTimeout(function () {
    popup.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function restaurant_food_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  var popup1 = $('#modal_info_item');
  var popup2 = $('#modal_food_add');

  axios.post('/admin/restaurant/food/add', {
    name: form.find('input[name=name]').val(),
    category_name: form.find('input[name=category_name]').val(),
    restaurant_parent_id: form.find('input[name=restaurant_parent_id]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      var wrap_foods = popup1.find('.frm_restaurant_foods');
      wrap_foods.find('.frm_restaurant_foods_data .foods_empty').remove();
      wrap_foods.find('.frm_restaurant_foods_data').prepend(response.data.html);

      wrap_foods.find('.count_foods').text('(' + response.data.count_foods + ')');
      wrap_foods.find('.count_foods_1').text(response.data.count_foods_1);
      wrap_foods.find('.count_foods_2').text(response.data.count_foods_2);
      wrap_foods.find('.count_foods_3').text(response.data.count_foods_3);

      form_close(popup2);

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
    });

  return false;
}
function restaurant_food_recipe_prepare(ele) {
  var food_item = $(ele).closest('.data_food_item');
  var popup1 = $(ele).closest('.modal');
  var popup2 = $('#modal_food_ingredient_recipe');
  var form = popup2.find('form');

  var restaurant_parent_id = popup1.find('input[name=restaurant_parent_id]').val();
  var food_id = food_item.attr('data-food_id');

  popup2.find('input[name=restaurant_parent_id]').val(restaurant_parent_id);
  popup2.find('input[name=food_id]').val(food_id);

  form_loading(form);

  axios.post('/admin/restaurant/food/ingredient/get', {
    restaurant_parent_id: restaurant_parent_id,
    food_id: food_id,
    type: 'recipe',
  })
    .then(response => {

      if (response.data.html && response.data.html != '') {
        form.find('.wrap-edit-ingredients').removeClass('d-none');
        form.find('.wrap-add-item-ingredients .wrap-fetch').empty().append(response.data.html);
        bind_datad(form);
      }

      popup2.modal('show');

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
    });

  return false;
}
function restaurant_food_recipe(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var popup1 = $('#modal_info_item');
  var popup2 = $('#modal_food_ingredient_recipe');

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
  if (!ingredients.length) {
    message_from_toast('error', acmcfs.message_title_error, "Ingredients required", true);
    return false;
  }

  form_loading(form);

  var restaurant_parent_id = form.find('input[name=restaurant_parent_id]').val();
  var food_id = form.find('input[name=food_id]').val();

  axios.post('/admin/restaurant/food/ingredient/update', {
    restaurant_parent_id: restaurant_parent_id,
    food_id: food_id,
    type: 'recipe',
    ingredients: ingredients,

  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      popup1.find('.data_food_item_' + food_id + ' .food_ingredient_recipe').empty().append(response.data.html);

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
      form_close(popup2);
    });

  return false;
}
function restaurant_food_robot_prepare(ele, rpitd = 0) {
  var food_item = $(ele).closest('.data_food_item');
  var popup1 = $(ele).closest('.modal');
  var popup2 = $('#modal_food_ingredient_robot');
  var form = popup2.find('form');

  var restaurant_parent_id = popup1.length ? popup1.find('input[name=restaurant_parent_id]').val() : 0;
  if (parseInt(rpitd)) {
    restaurant_parent_id = rpitd;
  }

  var food_id = food_item.attr('data-food_id');

  popup2.find('input[name=restaurant_parent_id]').val(restaurant_parent_id);
  popup2.find('input[name=food_id]').val(food_id);

  form_loading(form);

  axios.post('/admin/restaurant/food/ingredient/get', {
    restaurant_parent_id: restaurant_parent_id,
    food_id: food_id,
    type: 'robot',
  })
    .then(response => {

      if (response.data.html && response.data.html != '') {
        form.find('.wrap-edit-ingredients').removeClass('d-none');
        form.find('.wrap-add-item-ingredients .wrap-fetch').empty().append(response.data.html);
        bind_datad(form);
      }

      popup2.modal('show');

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
    });

  return false;
}
function restaurant_food_robot(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var popup1 = $('#modal_info_item');
  var popup2 = $('#modal_food_ingredient_robot');

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
  if (!ingredients.length) {
    message_from_toast('error', acmcfs.message_title_error, "Ingredients required", true);
    return false;
  }

  form_loading(form);

  var restaurant_parent_id = form.find('input[name=restaurant_parent_id]').val();
  var food_id = form.find('input[name=food_id]').val();

  axios.post('/admin/restaurant/food/ingredient/update', {
    restaurant_parent_id: restaurant_parent_id,
    food_id: food_id,
    type: 'robot',
    ingredients: ingredients,

  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      if (popup1.length) {
        popup1.find('.data_food_item_' + food_id + ' .food_ingredient_robot').empty().append(response.data.html);
      }
      else if ($('.tr_restaurant_food_' + restaurant_parent_id + '_' + food_id).length) {
        restaurant_food_serve_tr(restaurant_parent_id, food_id, response.data.datas);
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
      form_loading(form, false);
      form_close(popup2);
    });

  return false;
}
function restaurant_food_serve(itd) {

  axios.post('/admin/restaurant/food/serve', {
    restaurant_parent_id: itd,

  })
    .then(response => {

      // console.log(response.data.datas);
      if (response.data.datas.length) {
        response.data.datas.forEach(function (v, k) {
          if ($('.tr_restaurant_food_' + itd + '_' + v.food_id).length) {
            restaurant_food_serve_tr(itd, v.food_id, v);
          }
        });
      }

    })
    .catch(error => {
      console.log(error);
    });

  return false;
}
function restaurant_food_serve_tr(restaurant_parent_id, food_id, datas) {
  var html1 = '';
  var html2 = '---';
  var cls2 = '';
  var sel2 = '';
  var opt2 = '';
  var opt2_selected = '';
  var opt2_value = 0;
  var cor2 = '';

  if (datas.ingredients.length) {
    html2 = '';

    datas.ingredients.forEach(function (v1, k1) {
      cls2 = (v1.ingredient_type == 'core') ? 'text-danger cored' : 'text-dark';
      cor2 = ' - ' + v1.ingredient_quantity + ' ' + v1.name;

      if (acmcfs.user_role == 'superadmin' || parseInt(acmcfs.uid) == 5) {
        cor2 = '<span class="wrap_text_roboflow_ingredient cursor-pointer ' + cls2 + '" onclick="food_ingredient_core_quick(this, ' + v1.food_ingredient_id + ')">' +
          cor2 +
          '</span>';
      }

      for (opt2_value = 95; opt2_value >= 30;) {
        opt2_selected = (opt2_value == parseInt(v1.confidence)) ? ' selected="selected" ' : '';

        opt2 += '<option value="' + opt2_value + '" ' + opt2_selected + '>' + opt2_value + '%</option>';

        opt2_value = opt2_value - 5;
      }

      sel2 = '<select class="form-control p-1 acm-width-50-max" onchange="food_ingredient_confidence_quick(this, ' + v1.food_ingredient_id + ')">' +
        opt2 +
        '</select>';

      html2 += '<div>' +
        '<div class="d-inline-block">' +
        sel2 +
        '</div>' +
        '<div class="d-inline-block acm-ml-px-5">' +
        cor2 +
        '</div>' +
        '</div>';
    });
  }

  var confidence_rate = parseInt(datas.food_confidence) ? datas.food_confidence : 30;
  var confidence_group = 'Not Trained Yet';
  if (parseInt(datas.food_live_group) == 1) {
    confidence_group = 'Super Confidence';
  } else if (parseInt(datas.food_live_group) == 2) {
    confidence_group = 'Less Training';
  }

  html1 += '<div class="acm-clearfix position-relative data_food_item data_food_item_' + restaurant_parent_id + '_' + food_id + '" ' +
    ' data-food_id="' + food_id + '" ' +
    ' data-restaurant_parent_id="' + restaurant_parent_id + '" ' +
    ' data-live_group="' + datas.food_live_group + '" ' +
    ' data-confidence="' + datas.food_confidence + '" ' +
    ' data-category_name="' + datas.food_category_name + '" ' +
    ' >' +
    '<div class="acm-clearfix overflow-hidden mb-2">' +
    '<div class="acm-float-left w-100 mt-1">' +
    '<div class="form-floating form-floating-outline position-relative">' +
    '<button type="button" class="btn btn-sm btn-info p-1 position-absolute acm-right-0" onclick="restaurant_food_update_prepare(this, \'category_name\')">' +
    '<i class="mdi mdi-pencil"></i>' +
    '</button>' +
    '<input type="text" class="form-control text-center p-1" id="robo-category-' + restaurant_parent_id + '-' + food_id + '" name="category_name" disabled="disabled" value="' + datas.food_category_name + '">' +
    '<label class="text-dark fw-bold" for="robo-category-' + restaurant_parent_id + '-' + food_id + '">Category</label>' +
    '</div>' +
    '</div>' +
    '<div class="acm-float-right acm-w-30 mt-1">' +
    '<div class="form-floating form-floating-outline position-relative">' +
    '<button type="button" class="btn btn-sm btn-info p-1 position-absolute acm-right-0" onclick="restaurant_food_update_prepare(this, \'confidence\')">' +
    '<i class="mdi mdi-pencil"></i>' +
    '</button>' +
    '<input type="text" class="form-control text-center p-1" id="robo-confidence-' + restaurant_parent_id + '-' + food_id + '" name="confidence" disabled="disabled" value="' + confidence_rate + '">' +
    '<label class="text-dark fw-bold" for="robo-confidence-' + restaurant_parent_id + '-' + food_id + '">Rate</label>' +
    '</div>' +
    '</div>' +
    '<div class="acm-float-left acm-w-60 mt-1">' +
    '<div class="form-floating form-floating-outline position-relative">' +
    '<button type="button" class="btn btn-sm btn-info p-1 position-absolute acm-right-0" onclick="restaurant_food_update_prepare(this, \'live_group\')">' +
    '<i class="mdi mdi-pencil"></i>' +
    '</button>' +
    '<input type="text" class="form-control p-1" id="robo-group-' + restaurant_parent_id + '-' + food_id + '" name="live_group" disabled="disabled" value="' + confidence_group + '">' +
    '<label class="text-dark fw-bold" for="robo-group-' + restaurant_parent_id + '-' + food_id + '">Group</label>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<div class="position-absolute acm-right-0">' +
    '<button type="button" class="btn btn-danger p-1" onclick="restaurant_food_photo_prepare(this)">' +
    '<i class="mdi mdi-upload"></i> Upload Photo' +
    '</button>' +
    '</div>' +
    '<div class="mt-2"><img src="' + datas.food_photo + '" loading="lazy" class="w-100" id="food_photo_standard_' + restaurant_parent_id + '_' + food_id + '" /></div>' +
    '<div class="mb-1 mt-1">' +
    '<button type="button" class="btn btn-sm btn-warning p-1 d-inline-block acm-mr-px-5" onclick="restaurant_food_sync_prepare(this, \'robot\', ' + restaurant_parent_id + ')">' +
    '<i class="mdi mdi-sync"></i>' +
    '</button>' +
    '<button type="button" class="btn btn-sm btn-info p-1 d-inline-block" onclick="restaurant_food_robot_prepare(this, ' + restaurant_parent_id + ')">' +
    '<i class="mdi mdi-pencil"></i>' +
    '</button>' +
    '<div class="d-inline-block acm-ml-px-5">' +
    '<b class="text-primary">Roboflow Ingredients</b>' +
    '</div>' +
    '</div>' +
    '<div>' + html2 + '</div>' +
    '</div>';

  $('.tr_restaurant_food_' + restaurant_parent_id + '_' + food_id).empty().append(html1);
}
function restaurant_food_sync_prepare(ele, type, rpitd = 0) {
  var food_item = $(ele).closest('.data_food_item');
  var popup1 = $(ele).closest('.modal');
  var popup2 = $('#modal_food_ingredient_sync');
  var form = popup2.find('form');

  var restaurant_parent_id = popup1.length ? popup1.find('input[name=restaurant_parent_id]').val() : 0;
  if (parseInt(rpitd)) {
    restaurant_parent_id = rpitd;
  }

  var food_id = food_item.attr('data-food_id');

  popup2.find('input[name=restaurant_parent_id]').val(restaurant_parent_id);
  popup2.find('input[name=food_id]').val(food_id);
  popup2.find('input[name=type]').val(type);

  popup2.find('select[name=restaurant_parent_id]').selectize()[0].selectize.setValue('[]');

  popup2.modal('show');
}
function restaurant_food_sync(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var popup1 = $('#modal_info_item');
  var popup2 = $('#modal_food_ingredient_sync');

  var restaurants = popup2.find('select[name=restaurant_parent_id]').val();
  if (!restaurants.length) {
    message_from_toast('error', acmcfs.message_title_error, "Restaurants required", true);
    return false;
  }

  form_loading(form);

  axios.post('/admin/restaurant/food/sync', {
    restaurant_parent_id: popup2.find('input[name=restaurant_parent_id]').val(),
    food_id: popup2.find('input[name=food_id]').val(),
    type: popup2.find('input[name=type]').val(),
    restaurants: restaurants,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      if (restaurants.length) {
        restaurants.forEach(function (v, k) {
          if ($('.checker_restaurant_' + v).length) {
            restaurant_food_serve(v);
          }
        });
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
      form_loading(form, false);
      form_close(popup2);
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
    // rbf_scan: form.find('input[name=rbf_scan]').is(':checked') ? 1 : 0,
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
    // rbf_scan: form.find('input[name=rbf_scan]').is(':checked') ? 1 : 0,
    restaurant_parent_id: form.find('select[name=restaurant]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

      form_close(form);
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
function sensor_stats_view_food_categories() {
  sensor_stats_view('food_category', 0);
}
function sensor_stats_view_food_category(food_category_id) {
  sensor_stats_view('food_category', food_category_id);
}
function sensor_stats_view_foods() {
  sensor_stats_view('food', 0);
}
function sensor_stats_view_food(food_id) {
  sensor_stats_view('food', food_id);
}
function sensor_stats_view_ingredients() {
  sensor_stats_view('ingredient', 0);
}
function sensor_stats_view_ingredient(ingredient_id) {
  sensor_stats_view('ingredient', ingredient_id);
}
function sensor_stats_view_times() {
  sensor_stats_view('hour', 0);
}
function sensor_stats_view_time(hour) {
  sensor_stats_view('hour', hour);
}
function sensor_stats_view(type, item = 0) {
  var wrap = $('#wrap-stats-total');

  var times = wrap.find('input[name=search_time]').val();

  wrap.find('.wrap-search-condition').addClass('d-none');
  if (times && times !== '') {
    wrap.find('.wrap-search-condition').removeClass('d-none');
    wrap.find('.wrap-search-condition .search-time').empty().text(times);
  }

  axios.post('/admin/sensor/food/scan/view', {
    item: $('body input[name=current_restaurant]').val(),
    times: times,
    type: 'total',
    item_type: type,
    item_id: item,
  })
    .then(response => {

      if (response.data.ids.length) {
        var itd = response.data.itd;

        //sensor_food_scan_info
        var popup = $('#modal_food_scan_info');
        popup.find('input[name=popup_view_id_itm]').val(itd);

        var hidden_btns = true;
        if (response.data.ids.length > 1) {
          hidden_btns = false;
        }

        popup.find('input[name=popup_view_ids]').val(response.data.ids_string);

        popup.find('.acm-modal-arrow').removeClass('d-none');
        if (hidden_btns) {
          popup.find('.acm-modal-arrow').addClass('d-none');
        }

        popup.find('.modal-header h4').text('Loading...');
        popup.find('.modal-body').addClass('text-center').empty()
          .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

        axios.post('/admin/sensor/food/scan/info', {
          item: itd,
        })
          .then(response => {

            var title = response.data.sensor.name + ' <span class="badge acm-ml-px-10 bg-primary">ID: ' + response.data.rfs.id + '</span>';
            popup.find('.modal-header h4').empty().append(title);

            popup.find('.modal-body').removeClass('text-center').empty()
              .append(response.data.html_info);

            bind_datad(popup);
            popup.modal('show');

          })
          .catch(error => {
            console.log(error);
          });

      }

    })
    .catch(error => {
      console.log(error);
    });

  return false;
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

        html += '<li>' +
          '<a class="dropdown-item cursor-pointer" href="javascript:void(0);" onclick="sensor_stats_view_food_categories()">' +
          'View all photos ' +
          // '<b class="acm-mr-px-5">' + response.data.stats.category_error + '</b>' +
          '</a>' +
          '</li>';

        response.data.stats.category_error_list.forEach(function (v, k) {
          var title = v.food_category_name && v.food_category_name !== '' && v.food_category_name !== 'null'
            ? v.food_category_name : 'Not group food category yet';
          html += '<li>' +
            '<a class="dropdown-item" href="javascript:void(0);" onclick="sensor_stats_view_food_category(\'' + v.food_category_id + '\')">' +
            '<b class="acm-mr-px-5">' + v.total_error + '</b>' +
            title + '</a>' +
            '</li>';
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

        html += '<li>' +
          '<a class="dropdown-item cursor-pointer" href="javascript:void(0);" onclick="sensor_stats_view_foods()">' +
          'View all photos: ' +
          '<b class="acm-mr-px-5">' + response.data.stats.food_error + '</b>' +
          '</a>' +
          '</li>';

        response.data.stats.food_error_list.forEach(function (v, k) {
          var title = v.food_name && v.food_name !== '' && v.food_name !== 'null'
            ? v.food_name : 'No food found';

          html += '<li>' +
            '<a class="dropdown-item cursor-pointer" href="javascript:void(0);" onclick="sensor_stats_view_food(\'' + v.food_id + '\')">' +
            '<b class="acm-mr-px-5">' + v.total_error + '</b>' +
            v.food_name + '</a>' +
            '</li>';
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

        html += '<li>' +
          '<a class="dropdown-item cursor-pointer" href="javascript:void(0);" onclick="sensor_stats_view_ingredients()">' +
          'View all photos: ' +
          '<b class="acm-mr-px-5">' + response.data.stats.ingredient_missing + '</b>' +
          '</a>' +
          '</li>';

        response.data.stats.ingredient_missing_list.forEach(function (v, k) {
          var title = v.ingredient_name && v.ingredient_name !== '' && v.ingredient_name !== 'null'
            ? v.ingredient_name : 'No ingredient found';

          html += '<li>' +
            '<a class="dropdown-item" href="javascript:void(0);" onclick="sensor_stats_view_ingredient(\'' + v.ingredient_id + '\')">' +
            '<b class="acm-mr-px-5">' + v.total_error + '</b>' +
            v.ingredient_name + '</a>' +
            '</li>';
        });

        wrap.find('.stats-ingredients-missing-list').empty()
          .removeClass('d-none').append(html);
      }

      wrap.find('.stats-time-frames-count').text(response.data.stats.time_frame);

      wrap.find('.stats-time-frames-list').addClass('d-none');
      if (response.data.stats.time_frame_list.length) {
        var html = '';

        html += '<li>' +
          '<a class="dropdown-item cursor-pointer" href="javascript:void(0);" onclick="sensor_stats_view_times()">' +
          'View all photos: ' +
          '<b class="acm-mr-px-5">' + response.data.stats.time_frame + '</b>' +
          '</a>' +
          '</li>';

        response.data.stats.time_frame_list.forEach(function (v, k) {
          var title = v.hour_error < 10
            ? '0' + v.hour_error + ':00 - ' + '0' + v.hour_error + ':59'
            : v.hour_error + ':00 - ' + v.hour_error + ':59';

          html += '<li>' +
            '<a class="dropdown-item" href="javascript:void(0);" onclick="sensor_stats_view_time(\'' + v.hour_error + '\')">' +
            '<b class="acm-mr-px-5">' + v.total_error + '</b>' +
            title + '</a>' +
            '</li>';
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
  var customer_requested = form.find('input[name=customer_requested]').is(':checked') ? 1 : 0;
  var note_kitchen = form.find('input[name=note_kitchen]').is(':checked') ? 1 : 0;
  var food_multi = form.find('input[name=food_multi]').is(':checked') ? 1 : 0;
  var food_count = form.find('input[name=food_count]').val();

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
    rbf_error: form.find('input[name=rbf_error]').is(':checked') ? 1 : 0,
    missings: missings,
    texts: texts,
    customer_requested: customer_requested,
    note_kitchen: note_kitchen,
    food_multi: food_multi,
    food_count: input_number_only(food_count),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);
      sensor_food_scan_info_rebind(view_current);
      form_close(form);
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
    });

  return false;
}
function sensor_food_scan_update_food_multi(ele) {
  var parent = $(ele).closest('form');
  var bind = $(ele);

  parent.find('.food_count').addClass('d-none');
  if (bind.is(':checked')) {
    parent.find('.food_count').removeClass('d-none');
    parent.find('input[name=food_count]').val('');

    setTimeout(function () {
      parent.find('input[name=food_count]').focus();
    }, 500);
  }
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

      var title = response.data.sensor.name + ' <span class="badge acm-ml-px-10 bg-primary">ID: ' + response.data.rfs.id + '</span>';
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
  var popup2 = $('#modal_food_scan_info_update');
  if (popup2.hasClass('show')) {
    return false;
  }

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

      var title = response.data.sensor.name + ' <span class="badge acm-ml-px-10 bg-primary">ID: ' + response.data.rfs.id + '</span>';
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

      form_close(form);
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
    });

  return false;
}
//food category
function food_category_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/food-category/store', {
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
  form_loading(form);

  axios.post('/admin/food-category/update', {
    item: form.find('input[name=item]').val(),
    name: form.find('input[name=name]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

      form_close(form);
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

      form_close(form);
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
    rbf_food_confidence: form.find('input[name=rbf_food_confidence]').val(),
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

      form_close(form);
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
function user_zalo_prepare(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_zalo_sync');
  var selected = parseInt(tr.attr('data-zalo_id')) ? tr.attr('data-zalo_id') : '';

  popup.find('input[name=item]').val(tr.attr('data-id'));
  popup.find('select[name=zalo]').selectize()[0].selectize.setValue(selected);

}
function user_zalo(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var popup = form.closest('.modal');

  form_loading(form);

  axios.post('/admin/user/zalo/user/update', {
    item: form.find('input[name=item]').val(),
    zalo: form.find('select[name=zalo]').val(),
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

      form_loading(form, false);
      form_close(popup);
    });

  return false;
}
function user_zalo_message_prepare(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_zalo_message');
  var selected = parseInt(tr.attr('data-zalo_id')) ? tr.attr('data-zalo_id') : '';

  popup.find('input[name=item]').val(tr.attr('data-id'));

  popup.modal('show');
}
function user_zalo_message(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var popup = form.closest('.modal');

  form_loading(form);

  axios.post('/admin/user/zalo/message/send', {
    item: form.find('input[name=item]').val(),
    type: form.find('input[name=type]:checked').val(),
    message: form.find('textarea[name=message]').val(),
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

      form_loading(form, false);
      form_close(popup);
    });

  return false;
}
//rfs
function restaurant_food_scan_view(id) {
  axios.post('/admin/photo/view', {
    item: id,
  })
    .then(response => {
      //

      restaurant_food_scan_notes(id);
    })
    .catch(error => {
      //
    });

  return false;
}
function restaurant_food_scan_notes(id) {
  var wrap = $('#lcl_descr');

  axios.post('/admin/photo/note/get', {
    item: id,
  })
    .then(response => {

      var wrap = $('#lcl_descr');
      var noted = false;
      var html = '';
      var html_noter = '';

      var type = $('.result_photo_sensor').length ? 'kitchens' : '';

      if (response.data.note && response.data.note != '' && response.data.note != 'null') {
        noted = true;

        if (type == 'kitchens') {
          wrap.find('textarea[name=note]').val(response.data.note);
        }

        if (response.data.noter && response.data.noter.name) {
          html_noter = '<div class="text-dark acm-text-italic">(last edited by @' + response.data.noter.name + ')</div>';
        }

        html += '<div class="acm-clearfix position-relative p-2 acm-bg-efefef">' +
          '<div class="acm-float-right"></div>' +
          '<div class="text-dark fw-bold">Main Note: </div>' +
          '<div class="text-dark">' + bind_nl2br(response.data.note) + '</div>' +
          '<div class="acm-text-right">' + html_noter + '</div>' +
          '</div>';
      }

      if (response.data.comments && response.data.comments.length) {
        noted = true;

        response.data.comments.forEach(function (v, k) {
          html += '<div class="acm-clearfix position-relative acm-col-noted p-2">' +
            '<div class="acm-float-right acm-fs-12 acm-text-right">' +
            '<div class="text-dark">' + v.created_at_1 + '</div>' +
            '<div class="text-dark">' + v.created_at_2 + '</div>' +
            '</div>' +
            '<div class="text-dark fw-bold">@' + v.user_name + ': </div>' +
            '<div class="text-dark">' + bind_nl2br(v.user_noted) + '</div>' +
            '</div>';
        });
      }

      wrap.find('.wrap_notes').empty();
      if (noted) {
        wrap.find('.wrap_notes').append(html);
      }

      wrap.find('input[name=customer_requested]').prop('checked', false);
      if (response.data.customer_requested) {
        wrap.find('input[name=customer_requested]').prop('checked', true);
      }

      wrap.find('input[name=food_multi]').prop('checked', false);
      wrap.find('.food_count').addClass('d-none');
      wrap.find('input[name=food_count]').val('');
      if (response.data.count_foods) {
        wrap.find('input[name=food_multi]').prop('checked', true);
        wrap.find('.food_count').removeClass('d-none');
        wrap.find('input[name=food_count]').val(response.data.count_foods);
      }

      bind_datad(wrap);

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
function restaurant_food_scan_cmt(ele) {
  var parent = $('#lcl_descr');
  var content = parent.find('textarea[name=note]').val();
  var object_id = parent.find('input[name=object_id]').val();
  var customer_requested = parent.find('input[name=customer_requested]').is(':checked') ? 1 : 0;
  var food_multi = parent.find('input[name=food_multi]').is(':checked') ? 1 : 0;
  var food_count = parent.find('input[name=food_count]').val();
  var type = $('.result_photo_sensor').length ? 'kitchens' : '';

  axios.post('/admin/comment/note', {
    object_id: object_id,
    object_type: 'restaurant_food_scan',
    content: content,
    customer_requested: customer_requested,
    food_multi: food_multi,
    food_count: input_number_only(food_count),
    type: type,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      if ($('body .restaurant_food_scan_' + object_id).length) {
        $('body .restaurant_food_scan_' + object_id).attr('data-lcl-txt', content);
      }

      if ($('.result_photo_sensor').length) {
        $('.result_photo_sensor a').attr('data-lcl-txt', content);
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
function restaurant_food_scan_cmt_food(ele) {
  var parent = $('#lcl_descr');
  var bind = $(ele);

  parent.find('.food_count').addClass('d-none');
  if (bind.is(':checked')) {
    parent.find('.food_count').removeClass('d-none');
    parent.find('input[name=food_count]').val('');
  }
}
//food
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
  form_loading(form);

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

      form.find('.wrap-add-item-ingredients .wrap-fetch').prepend(response.data.html);
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

      form.find('.wrap-add-item-ingredients .wrap-fetch').prepend(response.data.html);
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
  form_loading(form);

  const formData = new FormData();
  formData.append("item", form.find('input[name=item]').val());
  formData.append("name", form.find('input[name=name]').val());
  formData.append("live_group", form.find('select[name=live_group]').val());

  axios.post('/admin/food/update', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

      form_close(form);
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
function food_ingredient_confidence_quick(ele, itd) {
  var bind = $(ele);

  axios.post('/admin/restaurant/food/confidence', {
    item: itd,
    confidence: parseInt(bind.val())
  })
    .then(response => {
      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);
    })
    .catch(error => {
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
//report
function report_add(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/report/store', {
    name: form.find('input[name=name]').val(),
    dates: form.find('input[name=dates]').val(),
    restaurant_parent_id: form.find('select[name=restaurant_parent_id]').val(),
  })
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

      setTimeout(function () {
        form.find('input[name=name]').focus();
      }, acmcfs.timeout_quick);
    });

  return false;
}
function report_edit_prepare(ele) {
  var tr = $(ele).closest('tr');
  var form = $('#offcanvas_edit_item form');

  form.find('input[name=name]').val('');

  form.find('input[name=item]').val(tr.attr('data-id'));
  form.find('input[name=name]').val(tr.attr('data-name'));
  form.find('select[name=restaurant_parent_id]').selectize()[0].selectize.setValue(tr.attr('data-restaurant_parent_id'));

  form.find('input[name=dates]').daterangepicker({
    timePicker: true,
    timePickerIncrement: 30,
    locale: {
      format: 'DD/MM/YYYY HH:mm',
    },
    timePicker24Hour: true,
    startDate: input_date_time(tr.attr('data-date_from')),
    endDate: input_date_time(tr.attr('data-date_to')),
  });

  setTimeout(function () {
    form.find('input[name=name]').focus();
  }, acmcfs.timeout_quick);
}
function report_edit(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  form_loading(form);

  const formData = new FormData();
  formData.append("item", form.find('input[name=item]').val());
  formData.append("name", form.find('input[name=name]').val());
  formData.append("dates", form.find('input[name=dates]').val());
  formData.append("restaurant_parent_id", form.find('select[name=restaurant_parent_id]').val());

  axios.post('/admin/report/update', formData)
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

      form_close(form);
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
    });

  return false;
}
function report_delete_confirm(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_delete_item');

  popup.find('input[name=item]').val(tr.attr('data-id'));
}
function report_delete(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  axios.post('/admin/report/delete', {
    item: popup.find('input[name=item]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      datatable_refresh();

      form_close(popup);
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
function report_start_confirm(ele) {
  var tr = $(ele).closest('tr');
  var popup = $('#modal_start_item');

  popup.find('input[name=item]').val(tr.attr('data-id'));
}
function report_start(ele) {
  var popup = $(ele).closest('.modal');
  form_loading(popup);

  var itd = popup.find('input[name=item]').val();

  axios.post('/admin/report/start', {
    item: itd,
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      report_info(itd);
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
function report_info(itd) {
  page_url(acmcfs.link_base_url + '/admin/report/info/' + itd);
}
function report_load(itd) {
  var wrap = $('#wrap-datas');

  axios.post('/admin/report/table', {
    item: itd,
  })
    .then(response => {

      wrap.empty().append(response.data.html);

      $('#not_found_dishes').text(response.data.not_found);

      bind_datad($('body'));
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
function report_photo_nf() {
  var popup = $('#modal_report_nf');
  var form = popup.find('form');

  var not_found = parseInt($('#not_found_dishes').text());
  if (!not_found) {
    return false;
  }

  popup.modal('show');

  popup.find('.modal-body .wrap_datas').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/report/photo/not-found', {
    item: popup.find('input[name=report_id]').val(),
  })
    .then(response => {

      report_photo_clear(form);

      popup.find('.view_current').val(1);
      popup.find('.view_all_count').text(response.data.ids.length);

      popup.find('input[name=popup_view_ids]').val(response.data.ids);

      //populate rfs
      if (parseInt(response.data.rfs.id)) {

        popup.find('input[name=popup_view_id_itm]').val(response.data.rfs.id);

        form.find('input[name=rbf_error]').prop('checked', false);
        if (response.data.rfs.rbf_error) {
          form.find('input[name=rbf_error]').prop('checked', true);
        }
        form.find('input[name=customer_requested]').prop('checked', false);
        if (response.data.rfs.customer_requested) {
          form.find('input[name=customer_requested]').prop('checked', true);
        }
        form.find('input[name=note_kitchen]').prop('checked', false);
        if (response.data.rfs.note_kitchen) {
          form.find('input[name=note_kitchen]').prop('checked', true);
        }

        form.find('.food_count').addClass('d-none');
        form.find('input[name=food_multi]').prop('checked', false);
        if (parseInt(response.data.rfs.count_foods) > 1) {
          form.find('input[name=food_multi]').prop('checked', true);

          form.find('.food_count').removeClass('d-none');
          form.find('input[name=food_count]').val(response.data.rfs.count_foods);
        }

        form.find('input[name=rfs]').val(response.data.rfs.id);
        form.find('select[name=point]').val(response.data.photo.point);
        form.find('textarea[name=note]').val(response.data.rfs.note);

        if (form.find('.wrap-texts .itm-text').length && response.data.rfs.texts.length) {
          form.find('.wrap-texts .itm-text input').each(function (k, v) {
            var input = $(v);
            var itd = parseInt(input.attr('data-itd'));
            if (response.data.rfs.texts.includes(itd)) {
              input.prop('checked', true);
            }
          });
        }

        form.find('.wrap-cmts').addClass('d-none');
        if (response.data.rfs.comments.length) {
          var html = '';

          response.data.rfs.comments.forEach(function (v, k) {
            html += '<div class="acm-clearfix position-relative acm-col-noted p-1">' +
              '<div class="acm-float-right acm-fs-12 acm-text-right">' +
              '<div class="text-dark">' + v.created_at_1 + '</div>' +
              '<div class="text-dark">' + v.created_at_2 + '</div>' +
              '</div>' +
              '<div class="text-dark fw-bold">@' + v.user_name + ': </div>' +
              '<div class="text-dark">' + bind_nl2br(v.user_noted) + '</div>' +
              '</div>';
          });

          form.find('.wrap-cmts .datas').empty().append(html);
          form.find('.wrap-cmts').removeClass('d-none');
        }

        setTimeout(function () {
          form.find('select[name=food]').selectize()[0].selectize.setValue(response.data.rfs.food_id);

          if (response.data.rfs.ingredients.length) {
            form.find('input[name=missing]').click();

            setTimeout(function () {
              form.find('.wrap_ingredients_missing .datas .js-item-row').each(function (k, v) {
                var tr = $(v);
                var itd = parseInt(tr.attr('data-itd'));
                if (!response.data.rfs.ingredients.includes(itd)) {
                  tr.find('button').click();
                }
              });
            }, 888);
          }
        }, 888);
      }

      popup.find('.modal-body .wrap_datas').empty()
        .append(response.data.html);

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
function report_photo_nf_action(next = 0) {
  var popup = $('#modal_report_nf');
  var arr = popup.find('input[name=popup_view_ids]').val().split(',').filter(Boolean);
  var view_current = parseInt(popup.find('input[name=popup_view_id_itm]').val());
  var view_next = 0;
  var view_input = 1;

  if (arr.length) {
    switch (next) {
      case 1:
        for (var i = 0; i < arr.length; ++i) {
          if (parseInt(arr[i]) == view_current) {
            if (arr[i + 1]) {
              view_next = arr[i + 1];
            } else {
              view_next = arr[0];
            }
          }
        }

        if (view_next) {
          view_input = arr.indexOf(view_next) + 1;
        }
        break;

      case 2:
        var input = parseInt(popup.find('input[name=popup_view_input]').val());

        if (input > arr.length) {
          input = arr.length;
        }

        view_input = input;
        view_next = arr[input - 1];

        break;

      default:
        //0
        for (var i = 0; i < arr.length; ++i) {
          if (parseInt(arr[i]) == view_current) {
            if (arr[i - 1]) {
              view_next = arr[i - 1];
            } else {
              view_next = arr[arr.length - 1];
            }
          }
        }

        if (view_next) {
          view_input = arr.indexOf(view_next) + 1;
        }
    }

    popup.find('input[name=popup_view_input]').val(view_input);
    popup.find('input[name=popup_view_id_itm]').val(view_next);

    //rebind
    report_photo_nf_itm(view_next);
  }
}
function report_photo_nf_update_prepare(ele) {
  var popup = $('#modal_photo_update');
  popup.modal('show');
}
function report_photo_nf_update(ele) {
  var popup = $(ele).closest('.modal');
  var form = $('#modal_report_nf').find('form');
  form_loading(popup);

  var missing = 0;
  var ingredients = [];

  if (form.find('input[name=missing]').is(':checked')) {

    missing = 1;

    form.find('.wrap_ingredients_missing .datas .js-item-row').each(function (k, v) {
      var tr = $(v);
      ingredients.push({
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

  axios.post('/admin/report/photo/update', {
    item: form.find('input[name=item]').val(), //report_id
    rfs: form.find('input[name=rfs]').val(), //rfs_id
    food: form.find('select[name=food]').val(),
    point: form.find('select[name=point]').val(),
    missing: missing,
    ingredients: ingredients,
    note: form.find('textarea[name=note]').val(),
    texts: texts,
    rbf_error: form.find('input[name=rbf_error]').is(':checked') ? 1 : 0,
    customer_requested: form.find('input[name=customer_requested]').is(':checked') ? 1 : 0,
    note_kitchen: form.find('input[name=note_kitchen]').is(':checked') ? 1 : 0,
    food_multi: form.find('input[name=food_multi]').is(':checked') ? 1 : 0,
    food_count: form.find('input[name=food_count]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);

      report_load(response.data.item.id);
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
function report_photo_nf_clear_prepare(ele) {
  var popup = $('#modal_photo_clear');
  popup.modal('show');
}
function report_photo_nf_clear(ele) {
  var popup = $(ele).closest('.modal');
  var form = $('#modal_report_nf').find('form');
  form_loading(popup);

  axios.post('/admin/report/photo/clear', {
    item: form.find('input[name=item]').val(), //report_id
    rfs: form.find('input[name=rfs]').val(), //rfs_id
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update, true);
      report_photo_clear(form);
      report_load(response.data.item.id);
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
function report_photo_nf_food_select() {
  var popup = $('#modal_report_nf');
  var form = popup.find('form');

  form.find('.wrap_ingredients_missing').addClass('d-none');

  var food = parseInt(form.find('select[name=food]').val());
  if (!food) {

    form.find('.wrap_ingredients_missing .datas').empty();

    return false;
  }

  axios.post('/admin/report/photo/food', {
    'item': form.find('input[name=item]').val(),
    'food': food,
  })
    .then(response => {

      form.find('.wrap_ingredients_missing .datas').empty().append(response.data.html);

      report_photo_nf_ingredient_missing();

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
function report_photo_nf_ingredient_missing() {
  var popup = $('#modal_report_nf');
  var form = popup.find('form');

  form.find('.wrap_ingredients_missing').addClass('d-none');

  if (form.find('input[name=missing]').is(':checked')) {
    form.find('.wrap_ingredients_missing').removeClass('d-none');
  }
}
function report_photo_clear(frm) {
  var form = $(frm);

  form.find('select[name=food]').selectize()[0].selectize.setValue('');
  form.find('select[name=point]').val('');
  form.find('textarea[name=note]').val('');

  form.find('input[name=missing]').prop('checked', false);
  form.find('.wrap_ingredients_missing').addClass('d-none');

  if (form.find('.wrap-texts .itm-text').length) {
    form.find('.wrap-texts .itm-text input').each(function (k, v) {
      var input = $(v);
      input.prop('checked', false);
    });
  }
}
function report_photo_nf_itm(itd) {
  var popup = $('#modal_report_nf');
  var form = popup.find('form');

  popup.find('.modal-body .wrap_datas').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/report/photo/rfs', {
    item: popup.find('input[name=report_id]').val(),
    rfs: itd, //rfs_id
  })
    .then(response => {

      report_photo_clear(form);

      //populate rfs
      if (parseInt(response.data.rfs.id)) {

        popup.find('input[name=popup_view_id_itm]').val(response.data.rfs.id);

        form.find('input[name=rbf_error]').prop('checked', false);
        if (response.data.rfs.rbf_error) {
          form.find('input[name=rbf_error]').prop('checked', true);
        }

        form.find('input[name=customer_requested]').prop('checked', false);
        if (response.data.rfs.customer_requested) {
          form.find('input[name=customer_requested]').prop('checked', true);
        }
        form.find('input[name=note_kitchen]').prop('checked', false);
        if (response.data.rfs.note_kitchen) {
          form.find('input[name=note_kitchen]').prop('checked', true);
        }

        form.find('.food_count').addClass('d-none');
        form.find('input[name=food_multi]').prop('checked', false);
        if (parseInt(response.data.rfs.count_foods) > 1) {
          form.find('input[name=food_multi]').prop('checked', true);

          form.find('.food_count').removeClass('d-none');
          form.find('input[name=food_count]').val(response.data.rfs.count_foods);
        }

        form.find('input[name=rfs]').val(response.data.rfs.id);
        form.find('select[name=point]').val(response.data.photo.point);
        form.find('textarea[name=note]').val(response.data.rfs.note);

        if (form.find('.wrap-texts .itm-text').length && response.data.rfs.texts.length) {
          form.find('.wrap-texts .itm-text input').each(function (k, v) {
            var input = $(v);
            var itd = parseInt(input.attr('data-itd'));
            if (response.data.rfs.texts.includes(itd)) {
              input.prop('checked', true);
            }
          });
        }

        form.find('.wrap-cmts').addClass('d-none');
        if (response.data.rfs.comments.length) {
          var html = '';

          response.data.rfs.comments.forEach(function (v, k) {
            html += '<div class="acm-clearfix position-relative acm-col-noted p-1">' +
              '<div class="acm-float-right acm-fs-12 acm-text-right">' +
              '<div class="text-dark">' + v.created_at_1 + '</div>' +
              '<div class="text-dark">' + v.created_at_2 + '</div>' +
              '</div>' +
              '<div class="text-dark fw-bold">@' + v.user_name + ': </div>' +
              '<div class="text-dark">' + bind_nl2br(v.user_noted) + '</div>' +
              '</div>';
          });

          form.find('.wrap-cmts .datas').empty().append(html);
          form.find('.wrap-cmts').removeClass('d-none');
        }

        setTimeout(function () {
          form.find('select[name=food]').selectize()[0].selectize.setValue(response.data.rfs.food_id);

          if (response.data.rfs.ingredients.length) {
            form.find('input[name=missing]').click();

            setTimeout(function () {
              form.find('.wrap_ingredients_missing .datas .js-item-row').each(function (k, v) {
                var tr = $(v);
                var itd = parseInt(tr.attr('data-itd'));
                if (!response.data.rfs.ingredients.includes(itd)) {
                  tr.find('button').click();
                }
              });
            }, 888);
          }
        }, 888);
      }

      popup.find('.modal-body .wrap_datas').empty()
        .append(response.data.html);

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
function report_photo_nf_full(food_id, type) {
  var popup = $('#modal_report_nf');
  var form = popup.find('form');

  popup.modal('show');

  popup.find('.modal-body .wrap_datas').addClass('text-center').empty()
    .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

  axios.post('/admin/report/photo/not-found', {
    item: popup.find('input[name=report_id]').val(),
    food_id: food_id,
    type: type,
  })
    .then(response => {

      report_photo_clear(form);

      popup.find('.view_current').val(1);
      popup.find('.view_all_count').text(response.data.ids.length);

      popup.find('input[name=popup_view_ids]').val(response.data.ids);

      //populate rfs
      if (parseInt(response.data.rfs.id)) {

        popup.find('input[name=popup_view_id_itm]').val(response.data.rfs.id);

        form.find('input[name=rbf_error]').prop('checked', false);
        if (response.data.rfs.rbf_error) {
          form.find('input[name=rbf_error]').prop('checked', true);
        }

        form.find('input[name=customer_requested]').prop('checked', false);
        if (response.data.rfs.customer_requested) {
          form.find('input[name=customer_requested]').prop('checked', true);
        }
        form.find('input[name=note_kitchen]').prop('checked', false);
        if (response.data.rfs.note_kitchen) {
          form.find('input[name=note_kitchen]').prop('checked', true);
        }

        form.find('.food_count').addClass('d-none');
        form.find('input[name=food_multi]').prop('checked', false);
        if (parseInt(response.data.rfs.count_foods) > 1) {
          form.find('input[name=food_multi]').prop('checked', true);

          form.find('.food_count').removeClass('d-none');
          form.find('input[name=food_count]').val(response.data.rfs.count_foods);
        }

        form.find('input[name=rfs]').val(response.data.rfs.id);
        form.find('select[name=point]').val(response.data.photo.point);
        form.find('textarea[name=note]').val(response.data.rfs.note);

        if (form.find('.wrap-texts .itm-text').length && response.data.rfs.texts.length) {
          form.find('.wrap-texts .itm-text input').each(function (k, v) {
            var input = $(v);
            var itd = parseInt(input.attr('data-itd'));
            if (response.data.rfs.texts.includes(itd)) {
              input.prop('checked', true);
            }
          });
        }

        form.find('.wrap-cmts').addClass('d-none');
        if (response.data.rfs.comments.length) {
          var html = '';

          response.data.rfs.comments.forEach(function (v, k) {
            html += '<div class="acm-clearfix position-relative acm-col-noted p-1">' +
              '<div class="acm-float-right acm-fs-12 acm-text-right">' +
              '<div class="text-dark">' + v.created_at_1 + '</div>' +
              '<div class="text-dark">' + v.created_at_2 + '</div>' +
              '</div>' +
              '<div class="text-dark fw-bold">@' + v.user_name + ': </div>' +
              '<div class="text-dark">' + bind_nl2br(v.user_noted) + '</div>' +
              '</div>';
          });

          form.find('.wrap-cmts .datas').empty().append(html);
          form.find('.wrap-cmts').removeClass('d-none');
        }

        setTimeout(function () {
          form.find('select[name=food]').selectize()[0].selectize.setValue(response.data.rfs.food_id);

          if (response.data.rfs.ingredients.length) {
            form.find('input[name=missing]').click();

            setTimeout(function () {
              form.find('.wrap_ingredients_missing .datas .js-item-row').each(function (k, v) {
                var tr = $(v);
                var itd = parseInt(tr.attr('data-itd'));
                if (!response.data.rfs.ingredients.includes(itd)) {
                  tr.find('button').click();
                }
              });
            }, 888);
          }
        }, 888);
      }

      popup.find('.modal-body .wrap_datas').empty()
        .append(response.data.html);

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
//mobi
function mobi_photo_multi_food(ele) {
  var popup = $('#modal_photo_cmt');
  var bind = $(ele);

  popup.find('.food_count').addClass('d-none');
  if (bind.is(':checked')) {
    popup.find('.food_count').removeClass('d-none');
    popup.find('input[name=food_count]').val('');
  }
}
function mobi_photo_view(itd) {
  var popup = $('#modal_photo_cmt');

  popup.find('input[name=item]').val(itd);
  popup.find('textarea[name=note]').val('');

  popup.find('.wrap_notes').addClass('d-none');

  popup.find('.modal-title').text('Photo Note ID: ' + itd);

  axios.post('/admin/photo/note/get', {
    item: itd,
  })
    .then(response => {

      var noted = false;
      var html = '';
      var html_noter = '';

      var type = $('.result_photo_sensor').length ? 'kitchens' : '';

      if (response.data.user_comment && response.data.user_comment != '' && response.data.user_comment != 'null') {
        popup.find('textarea[name=note]').val(response.data.user_comment);
      }

      if (response.data.note && response.data.note != '' && response.data.note != 'null') {
        noted = true;

        if (type == 'kitchens') {
          popup.find('textarea[name=note]').val(response.data.note);
        }

        if (response.data.noter && response.data.noter.name) {
          html_noter = '<div class="text-dark acm-text-italic">(last edited by @' + response.data.noter.name + ')</div>';
        }

        html += '<div class="acm-clearfix position-relative p-2 acm-bg-efefef">' +
          '<div class="acm-float-right"></div>' +
          '<div class="text-dark fw-bold">Main Note: </div>' +
          '<div class="text-dark">' + bind_nl2br(response.data.note) + '</div>' +
          '<div class="acm-text-right">' + html_noter + '</div>' +
          '</div>';
      }

      if (response.data.comments && response.data.comments.length) {
        noted = true;

        response.data.comments.forEach(function (v, k) {
          html += '<div class="acm-clearfix position-relative acm-col-noted p-2">' +
            '<div class="acm-float-right acm-fs-12 acm-text-right">' +
            '<div class="text-dark">' + v.created_at_1 + '</div>' +
            '<div class="text-dark">' + v.created_at_2 + '</div>' +
            '</div>' +
            '<div class="text-dark fw-bold">@' + v.user_name + ': </div>' +
            '<div class="text-dark">' + bind_nl2br(v.user_noted) + '</div>' +
            '</div>';
        });
      }

      popup.find('.wrap_notes').empty();
      if (noted) {
        popup.find('.wrap_notes').removeClass('d-none');
        popup.find('.wrap_notes').append(html);
      }

      popup.find('input[name=customer_requested]').prop('checked', false);
      if (response.data.customer_requested) {
        popup.find('input[name=customer_requested]').prop('checked', true);
      }

      popup.find('input[name=food_multi]').prop('checked', false);
      popup.find('.food_count').addClass('d-none');
      popup.find('input[name=food_count]').val('');
      if (response.data.count_foods) {
        popup.find('input[name=food_multi]').prop('checked', true);
        popup.find('.food_count').removeClass('d-none');
        popup.find('input[name=food_count]').val(response.data.count_foods);
      }

      popup.modal('show');

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
function mobi_photo_cmt(evt, frm) {
  evt.preventDefault();
  var form = $(frm);
  var popup = $('#modal_photo_cmt');

  var customer_requested = form.find('input[name=customer_requested]').is(':checked') ? 1 : 0;
  var food_multi = form.find('input[name=food_multi]').is(':checked') ? 1 : 0;
  var food_count = form.find('input[name=food_count]').val();

  form_loading(form);

  axios.post('/admin/comment/note', {
    object_id: form.find('input[name=item]').val(),
    object_type: 'restaurant_food_scan',
    content: form.find('textarea[name=note]').val(),
    customer_requested: customer_requested,
    food_multi: food_multi,
    food_count: input_number_only(food_count),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      form_loading(form, false);
      form_close(popup);
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
//kas
function kas_food_sync() {
  var popup = $('#modal_sync_confirm');

  popup.modal('show');
}
function kas_food_sync_confirm() {
  var popup = $('#modal_sync_confirm');
  form_loading(popup);

  axios.post('/admin/kas/food/get', {

  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();

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
      //end
      form_loading(popup, false);
      form_close(popup);
    });

  return false;
}
function kas_food(itd) {
  var popup = $('#modal_sync_food');

  popup.find('input[name=item]').val(itd);
  popup.find('form select').selectize()[0].selectize.setValue('');

  popup.modal('show');
}
function kas_food_confirm(evt, frm) {
  var popup = $('#modal_sync_food');
  var form = $(frm);
  form_loading(form);

  axios.post('/admin/kas/food/item', {
    item: form.find('input[name=item]').val(),
    food: form.find('select[name=food]').val(),
  })
    .then(response => {

      message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_add, true);

      datatable_refresh();

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
      //end
      form_loading(form, false);
      form_close(popup);
    });

  return false;
}
function kas_date_check(ele) {
  var bind = $(ele);
  var date = bind.val();

  if (!date || date == '') {
    return false;
  }

  var table = $('#table_checker');

  table.find('.td_restaurant').each(function (k, v) {
    var bind = $(v);
    var itd = bind.attr('data-value');

    kas_date_check_restaurant(itd, date);
  });
}
function kas_date_check_restaurant(itd, date) {
  var table = $('#table_checker');

  table.find('.td_restaurant_' + itd + ' button').addClass('d-none');

  axios.post('/admin/kas/date/check', {
    date: date,
    restaurant: itd,
  })
    .then(response => {

      table.find('.td_restaurant_' + itd + ' button .total_orders').text(response.data.total_orders);
      if (response.data.total_orders) {
        table.find('.td_restaurant_' + itd + ' button.total_orders').removeClass('d-none');
      }

      table.find('.td_restaurant_' + itd + ' button .total_photos').text(response.data.total_photos);
      if (response.data.total_photos) {
        table.find('.td_restaurant_' + itd + ' button.total_photos').removeClass('d-none');
      }

    })
    .catch(error => {
      console.log(error);
    });

  return false;
}
function kas_date_check_restaurant_data(itd, date_text = '') {
  var date = $('#kas-date-check').val();
  if (!date_text || date_text == '') {
    date_text = date;
  }

  var popup = $('#modal_checker_restaurant_date');

  axios.post('/admin/kas/date/check/restaurant', {
    date: date_text,
    restaurant: itd,
  })
    .then(response => {

      var html_title = response.data.restaurant.name + '<span class="badge acm-ml-px-10 bg-primary">' + date_text + '</span>';
      popup.find('.modal-title').empty().append(html_title);

      popup.find('.modal-body').empty().append(response.data.html);

      popup.modal('show');

      setTimeout(function () {
        popup.find('#wrap_hour_bill .hour_bill_hour button')[0].click();
        popup.find('#wrap_hour_photo .hour_photo_hour button')[0].click();
      }, 333);

    })
    .catch(error => {
      console.log(error);
    });

  return false;
}
function kas_date_check_restaurant_data_photo(itd, date_text = '') {
  var date = $('#kas-date-check').val();
  if (!date_text || date_text == '') {
    date_text = date;
  }

  var popup = $('#modal_checker_restaurant_date');

  axios.post('/admin/kas/date/check/restaurant/photo', {
    date: date_text,
    restaurant: itd,
  })
    .then(response => {

      kas_date_check_restaurant_data_photo_id(response.data.itd, response.data.total_items, response.data.items);

    })
    .catch(error => {
      console.log(error);
    });

  return false;
}
function kas_date_check_restaurant_data_photo_id(id, total = 0, ids = '') {
  var popup = $('#modal_food_scan_info');
  popup.find('input[name=popup_view_id_itm]').val(id);
  popup.find('input[name=popup_view_ids]').val(ids);

  var hidden_btns = true;
  if (total) {
    hidden_btns = false;
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

      var title = response.data.sensor.name + ' <span class="badge acm-ml-px-10 bg-primary">ID: ' + response.data.rfs.id + '</span>';
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
function kas_date_check_restaurant_data_hour_bill(hour, date, restaurant) {
  var wrap = $('#wrap_hour_bill');

  wrap.find('.hour_bill_hour button').removeClass('btn-primary').addClass('btn-secondary');
  wrap.find('.hour_bill_hour_' + hour + ' button').addClass('btn-primary').removeClass('btn-secondary');

  axios.post('/admin/kas/date/check/restaurant/hour', {
    hour: hour,
    date: date,
    restaurant: restaurant,
    type: 'bill',
  })
    .then(response => {

      wrap.find('.hour_bill_datas').empty()
        .append(response.data.html);

      bind_datad(wrap);

      setTimeout(function () {
        wrap.find('.hour_bill_datas .hour_bill_content').first().find('button')[0].click();
      }, 333);

    })
    .catch(error => {
      console.log(error);
    });

  return false;
}
function kas_date_check_restaurant_data_hour_photo(hour, date, restaurant) {
  var wrap = $('#wrap_hour_photo');

  wrap.find('.hour_photo_hour button').removeClass('btn-primary').addClass('btn-secondary');
  wrap.find('.hour_photo_hour_' + hour + ' button').addClass('btn-primary').removeClass('btn-secondary');

  axios.post('/admin/kas/date/check/restaurant/hour', {
    hour: hour,
    date: date,
    restaurant: restaurant,
    type: 'photo',
  })
    .then(response => {

      wrap.find('.hour_photo_datas').empty()
        .append(response.data.html);

      bind_datad(wrap);

      setTimeout(function () {

        var galleryThumbs = document.querySelector('.gallery-thumbs'),
          galleryTop = document.querySelector('.gallery-top');
        let galleryInstance;

        if (galleryThumbs) {
          galleryInstance = new Swiper(galleryThumbs, {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesVisibility: true,
            watchSlidesProgress: true
          });
        }

        if (galleryTop) {
          new Swiper(galleryTop, {
            spaceBetween: 10,
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev'
            },
            thumbs: {
              swiper: galleryInstance
            }
          });
        }

        lc_lightbox('.acm-lightbox-photo', {
          wrap_class: 'lcl_fade_oc',
          thumb_attr: 'data-lcl-thumb',
        });

      }, 333);

    })
    .catch(error => {
      console.log(error);
    });

  return false;
}
function kas_date_check_restaurant_data_hour_bill_item(ele, bill) {
  var bind = $(ele);
  var parent = bind.closest('.hour_bill_datas');

  parent.find('.hour_bill_content button').removeClass('btn-primary').addClass('btn-outline-primary');
  parent.find('.hour_bill_content_' + bill + ' button').addClass('btn-primary').removeClass('btn-outline-primary');

  parent.find('.hour_bill_content_data').addClass('d-none')
  parent.find('.hour_bill_content_data_' + bill).removeClass('d-none')
}
function kas_date_check_search(evt, frm) {
  var table = $('#table_checker_month');
  var form = $(frm);
  form_loading(form);

  table.find('tbody').empty();

  axios.post('/admin/kas/date/check/month', {
    month: form.find('select[name=month]').val(),
    year: form.find('select[name=year]').val(),
  })
    .then(response => {

      table.find('tbody').append(response.data.html);

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
      //end
      form_loading(form, false);
    });

  return false;
}

