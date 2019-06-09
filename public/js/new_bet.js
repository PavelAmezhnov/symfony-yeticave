$(document).ready(function() {
    if ($("#new-bet-form").length === 1) {
        var newBetForm = $("#new-bet-form")[0];

        $(newBetForm).find("button").on("click", function(e) {
            var minCost = $(newBetForm).find("input[name=min-cost]").val();
            var cost = $(newBetForm).find("input[name=cost]").val();
            
            if (Number(minCost) > Number(cost)) {
                $("#modal").html("Ставка не может быть ниже " + minCost).modal("show");
            } else {
                var id = $(newBetForm).find("input[name=id]").val();
                
                $.ajax({
                    url: newBetForm.action,
                    method: "POST",
                    data: {
                        id: id,
                        value: cost
                    }
                })
                .done(function(html) {
                    $(".lot-item__right").html(html);
                })
                .fail(function(jqXHR) {
                    $("#modal").html(jqXHR.responseJSON).modal("show");
                });
            }
        });
    }
});