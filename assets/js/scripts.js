jQuery(document).ready(function($) {
    var pricePerDay = parseFloat($('#price_per_day').val());

    function updateTotalPrice() {
        var basePrice = pricePerDay;
        var selectedExtras = $('input[name="extra_addons[]"]:checked');
        var extraTotal = 0;

        selectedExtras.each(function() {
            extraTotal += parseFloat($(this).data('price'));
        });

        var totalPrice = basePrice + extraTotal;

        $('#calculated_price').text('$' + totalPrice.toFixed(2));
        $('#total_price').val(totalPrice);
    }

    $(".datepicker").flatpickr({
        mode: "range",
        dateFormat: "d-M-y",
        onClose: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                var startDate = selectedDates[0];
                var endDate = selectedDates[1];

                var timeDiff = endDate.getTime() - startDate.getTime();
                var dayCount = Math.ceil(timeDiff / (1000 * 3600 * 24));

                if (dayCount > 0) {
                    pricePerDay *= dayCount;
                    updateTotalPrice();

                    $('#date_duration').html(
                        "Duration: <strong>" + dayCount + " days</strong>"
                    );
                } else {
                    $('#date_duration').html('');
                    $('#calculated_price').text('$0.00');
                    $('#total_price').val(0);
                }
            }
        }
    });

    $('input[name="extra_addons[]"]').change(function() {
        updateTotalPrice();
    });
});
