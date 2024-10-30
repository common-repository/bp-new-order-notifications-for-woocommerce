(function ($) {
    'use strict';

    var intervalHandle = null;
    var toast_position = 'right';
    var toast = true;
    if (js_args.popup_mode == 'false') {
        toast_position = 'center';
        toast = false;
    }
    function notification_ajax() {

        $.ajax({
            url: js_args.ajax_url,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'bp_new_order_notification',
                nonce: js_args.ajax_nonce,

            },
            success: function (res) {
                console.log(res.data.has_order);


                if (res.success && res.data.has_order != 0) {

                    Swal.fire({
                        title: res.data.title,
                        html: res.data.additional_txt,

                        target: '#bp-notification-timer',
                        customClass: {
                            container: 'position-absolute'
                        },
                        toast: toast,
                        position: 'bottom-' + toast_position,

                        showConfirmButton: true,
                        confirmButtonText: 'View Order',
                        showCloseButton: true,
                        icon: 'info',

                        timerProgressBar: true,

                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {
                            window.open(res.data.order_edit_link, '_blank');
                        }
                    });
                    var nofication_sound = new Audio(js_args.notifictaion_daily_sound);
                    nofication_sound.play();

                    bp_recent_order_table(res.data.list);

                }




            },
            error: function () {
                console.log('Ajax Error: New Order Notification');
            }
        });


    }
    function bp_recent_order_table($data_src) {
        var table = $('#new-order-table').DataTable();

        table.destroy();

        $('#new-order-table').DataTable({
            data: $data_src,
            retrieve: false,
            searching: false,
            "autoWidth": false,
            "order": [0, 'desc'],
            paging: false,
            "columnDefs": [
                { "width": "100", "targets": [0, 1, 2] }
            ],

            fixedColumns: true,
            columns: [
                { data: 'order_id' },
                { data: 'order_date' },
                { data: 'order_status' },
                { data: 'action' },
            ]
        });
    }

    function offline_nitifications() {
        $.ajax({
            url: js_args.ajax_url,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'bp_new_order_offline_notification',
                nonce: js_args.ajax_nonce,

            },
            success: function (res) {
                console.log(res.data.list);

                bp_recent_order_table(res.data.list);

                if (res.success && res.data.offline_sales != 0 && js_args.offline_notfication == 1) {
                    Swal.fire({
                        title: 'New Orders!',
                        html:
                            'You just made ' + res.data.offline_sales + ' sales today while you were away.',

                        target: '#bp-notification-timer',
                        customClass: {
                            container: 'position-absolute'
                        },
                        toast: toast,
                        position: 'bottom-' + toast_position,
                        timer: 5000,
                        showConfirmButton: false,
                        showCloseButton: true,
                        icon: 'info',

                        timerProgressBar: true,

                    });
                    var nofication_sound = new Audio(js_args.notifictaion_daily_sound);
                    nofication_sound.play();
                }



            },
            error: function () {
                console.log('Ajax Error: New Order Oflline Notification');
            }
        });
    }

    $(document).ready(function () {


        $("#wp-admin-bar-new-order-notification-enable").on("click", function (e) {
            $(document).find('#wp-admin-bar-new-order-notify > .ab-empty-item').addClass('hightlight-notification');
            $(document).find('#wp-admin-bar-new-order-notify').removeClass('hover');
            // intervalHandle;
            intervalHandle = setInterval(() => {
                notification_ajax();
            }, js_args.refresh_timer);
            console.log('1');
            offline_nitifications();
            e.preventDefault();
        });
        $("#wp-admin-bar-new-order-notification-disable").on("click", function (e) {
            $(document).find('#wp-admin-bar-new-order-notify > .ab-empty-item').removeClass('hightlight-notification');
            $(document).find('#wp-admin-bar-new-order-notify').removeClass('hover');
            // When you want to cancel it:
            clearInterval(intervalHandle);
            console.log('disbale');
            e.preventDefault();
        });

        $("#alert-btn-page-notification").on("click", function (e) {
            $(document).find('#wp-admin-bar-new-order-notify > .ab-empty-item').addClass('hightlight-notification');
            $(document).find('#wp-admin-bar-new-order-notify').removeClass('hover');
            $(this).attr('disabled', 'disabled');
            intervalHandle = setInterval(() => {
                notification_ajax();
            }, js_args.refresh_timer);

            console.log('1 si');
            offline_nitifications();

        });





    });


})(jQuery);

// Other code using $ as an alias to the other library
