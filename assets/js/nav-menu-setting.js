

/**
 * 1. check and set sub-menu and sub-menu-child css class for each menu item.
 * 2. change move arrow title 
 */

let menu_define = document.querySelectorAll('.menu .menu-item-wrapper');
menu_define.forEach((element) => {
    let element_id = element.getAttribute('id');
    let sub_menu = document.querySelectorAll('.menu #' + element_id + ' .sub-menu-item-wrapper > .menu-item-child');
    sub_menu.forEach((child) => {
        child.classList.add("sub-menu");
        let child_id = child.getAttribute('id');
        (document.querySelector('.menu #' + child_id + ' > .menu-item .btn-add-sub-menu')).setAttribute('title', 'Add sub menu child ');
        (document.querySelector('.menu #' + child_id + ' > .menu-item .btn-up-arrow')).setAttribute('title', 'Move sub menu up ');
        (document.querySelector('.menu #' + child_id + ' > .menu-item .btn-down-arrow')).setAttribute('title', 'Move sub menu down ');
        let child_sub_menu = document.querySelectorAll('.menu #' + child_id + ' .sub-menu-item-child-wrapper > .menu-item-child');
        child_sub_menu.forEach((e) => {
            e.classList.add("sub-menu-child");
            let child_id = e.getAttribute('id');
            (document.querySelector('.menu #' + child_id + ' > .menu-item .btn-up-arrow')).setAttribute('title', 'Move sub menu child up ');
            (document.querySelector('.menu #' + child_id + ' > .menu-item .btn-down-arrow')).setAttribute('title', 'Move sub menu child down ');
        });
    });
});


/**
 * Add menu
 */
$('.btn-add-menu').on('click', function (e) {
    e.preventDefault();
    let total_menu = $('.menu .menu-item-wrapper ').length + 1
    var menu_item = menu_item_wrapper();
    menu_item = menu_item.replaceAll('null-id', 'menu-' + total_menu);
    menu_item = menu_item.replaceAll('null-depth', '1');
    $('.menu-wrapper .menu').append(menu_item);
});

/**
 * Add sub-menu oe sub-menu-child
 */
$(document).on('click', '.btn-add-sub-menu', function (e) {
    e.preventDefault();
    var menu_item = menu_item_wrapper();
    let sort_id = $(this).attr("data-id");
    let depth = parseInt($('#' + sort_id).attr('itemdepth')) + 1;
    var class_name = 'menu-item-child';
    var total_sub_menu = add_menu_location_class = '';
    var item_child_count = 0;
    if (depth === 2) {
        class_name = class_name + ' sub-menu';
        total_sub_menu = 'total-sub-menu-child="0"';
        add_menu_location_class = ' .sub-menu-item-wrapper';
        item_child_count = $('#' + sort_id + add_menu_location_class + ' > .menu-item-child ').length + 1;
        menu_item = menu_item.replaceAll('sub-menu-item-wrapper', 'sub-menu-item-child-wrapper');
        menu_item = menu_item.replaceAll('Add sub menu', 'Add sub menu child');
        menu_item = menu_item.replaceAll('Move menu down', 'Move sub menu down');
        menu_item = menu_item.replaceAll('Move menu upward', 'Move sub menu upward');
    }
    if (depth === 3) {
        class_name = class_name + ' sub-menu-child';
        add_menu_location_class = ' .sub-menu-item-child-wrapper';
        item_child_count = $('#' + sort_id + add_menu_location_class + ' > .menu-item-child ').length + 1;
        menu_item = menu_item.replaceAll('sub-menu-item-wrapper', 'no-child-item');
        menu_item = menu_item.replaceAll('Move menu down', 'Move sub menu child down');
        menu_item = menu_item.replaceAll('Move menu upward', 'Move sub menu child upward');
    }
    menu_item = menu_item.replaceAll('null-id', sort_id + '-' + item_child_count);
    menu_item = menu_item.replaceAll('null-depth', depth);
    menu_item = menu_item.replaceAll('menu-item-wrapper', class_name);
    if (add_menu_location_class) {
        $('#' + sort_id + add_menu_location_class).append(menu_item);
    }

});

/**
 * Remove menu
 */
$(document).on('click', '.btn-remove', function (e) {
    e.stopPropagation();
    e.preventDefault();
    let sort_id = $(this).attr("data-id");
    $('#' + sort_id).remove();
});


// hide - show condition field
$(document).on('click', '.btn-add-condition', function (e) {
    e.stopPropagation();
    e.preventDefault();
    let sort_id = $(this).attr("data-id");
    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
    } else {
        $(this).addClass('active');
    }
    $('#' + sort_id + ' > .menu-item  .condition-fields').toggle();
});


/**
 * Change input checkbox target_blank value
 */
$(document).on('change', '.target_blank_no,.target_blank_yes', function () {
    let sort_id = $(this).parent().attr("data-id");
    if ($(this).val() == "0") {
        document.getElementById('target_yes_' + sort_id).checked = false
    }
    if ($(this).val() == "1") {
        document.getElementById('target_no_' + sort_id).checked = false
    }
});

/**
 * Moove up and down menu item
 */
$(document).on('click', '.btn-down-arrow, .btn-up-arrow', function (e) {
    e.stopPropagation();
    e.preventDefault();
    var current_id = $(this).attr("data-id");
    var current_id_split = current_id.split('-');
    var new_id = '';
    if ((current_id_split.length - 1) > 0) {
        for (let i = 0; i < current_id_split.length - 1; i++) {
            new_id = new_id + current_id_split[i] + '-';
        }
    }
    // to move down
    if ($(this).hasClass('btn-down-arrow')) {
        //get new section id
        new_id = new_id + (parseInt(current_id_split[(current_id_split.length - 1)]) + 1);
    }
    // to move up
    if ($(this).hasClass('btn-up-arrow')) {
        //get new section id
        new_id = new_id + (parseInt(current_id_split[(current_id_split.length - 1)]) - 1);
    }
    // get current section values
    let current_label = $('#' + current_id + ' .input-label').val();
    let current_link = $('#' + current_id + ' .input-link').val();
    let current_target_blank_no = $('#' + current_id + ' .target_blank_no').prop("checked");
    let current_target_blank_yes = $('#' + current_id + ' .target_blank_yes').prop("checked");
    let current_language = $('#' + current_id + ' .input-language').val();
    let current_user_role = $('#' + current_id + ' .user_role').val();
    // get new section values
    let new_label = $('#' + new_id + ' .input-label').val();
    let new_link = $('#' + new_id + ' .input-link').val();
    let new_target_blank_no = $('#' + new_id + ' .target_blank_no').prop("checked");
    let new_target_blank_yes = $('#' + new_id + ' .target_blank_yes').prop("checked");
    let new_language = $('#' + new_id + ' .input-language').val();
    let new_user_role = $('#' + new_id + ' .user_role').val();
    // set current section values into new section values place
    $('#' + new_id + ' .input-label').val(current_label);
    $('#' + new_id + ' .input-link').val(current_link);
    $('#' + new_id + ' .target_blank_no').prop("checked", current_target_blank_no);
    $('#' + new_id + ' .target_blank_yes').prop("checked", current_target_blank_yes);
    $('#' + new_id + ' .input-language').val(current_language);
    $('#' + new_id + ' .user_role').val(current_user_role);
    // set new section values into new current values place
    $('#' + current_id + ' .input-label').val(new_label);
    $('#' + current_id + ' .input-link').val(new_link);
    $('#' + current_id + ' .target_blank_no').prop("checked", new_target_blank_no);
    $('#' + current_id + ' .target_blank_yes').prop("checked", new_target_blank_yes);
    $('#' + current_id + ' .input-language').val(new_language);
    $('#' + current_id + ' .user_role').val(new_user_role);
});

// collapse or expand field
// $(document).on('click', '.btn-collapse-exp', function (e) {
//     e.stopPropagation();
//     e.preventDefault();
//     let sort_id = $(this).attr("data-id");
//     if ($(this).hasClass('expand')) {
//         $(this).removeClass('expand');
//         $(this).addClass('expand-off');
//         $('.menu-item[class*="' + sort_id + '"]').hide();
//     } else {
//         $(this).addClass('expand');
//         $(this).removeClass('expand-off');
//         $('.menu-item[class*="' + sort_id + '"]').show();
//     }
// });